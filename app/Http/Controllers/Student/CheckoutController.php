<?php

namespace App\Http\Controllers\Student;

use App\Actions\ActivateEnrollmentFromPaymentAction;
use App\Actions\CreateOrderAction;
use App\Actions\EnrollStudentAction;
use App\Exceptions\EnrollmentNotAllowedException;
use App\Http\Controllers\Controller;
use App\Models\Cohort;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PlatformSetting;
use App\Services\Payments\PaymentGatewayFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function create(Request $request, Cohort $cohort): Response|RedirectResponse
    {
        $cohort->load('course');

        if ($cohort->course->isFree()) {
            return $this->enrollFree($request, $cohort);
        }

        return Inertia::render('Student/Checkout/Show', [
            'cohort' => ['id' => $cohort->id, 'slug' => $cohort->slug, 'name' => $cohort->name, 'accepting_enrollment' => $cohort->isAcceptingEnrollment()],
            'course' => ['title' => $cohort->course->title, 'formatted_price' => $cohort->course->formattedPrice()],
        ]);
    }

    public function store(Request $request, Cohort $cohort): RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        $validated = $request->validate(['method' => 'required|in:paystack,flutterwave,bank_transfer']);

        $cohort->load('course');
        abort_if($cohort->course->isFree(), 422, 'This course is free — no checkout needed.');
        abort_unless($cohort->isAcceptingEnrollment(), 422, 'This cohort is not currently open for enrollment.');

        $order = (new CreateOrderAction())->execute($request->user(), $cohort);

        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'provider' => $validated['method'],
            'reference' => 'PAY-'.strtoupper(Str::random(12)),
            'status' => 'pending',
            'amount' => $order->amount,
            'currency' => $order->currency,
        ]);

        if ($validated['method'] === 'bank_transfer') {
            return to_route('checkout.bank-transfer', $order)->with('status', 'Order created — see transfer instructions below.');
        }

        $gateway = (new PaymentGatewayFactory())->make($validated['method']);
        $result = $gateway->initialize($order, $payment);

        return Inertia::location($result['authorization_url']);
    }

    /**
     * Paystack/Flutterwave redirect the browser BACK here after checkout —
     * this is a convenience UX path only. The actual source of truth is
     * verify(), called here AND independently by the webhook controllers;
     * activation never trusts the redirect itself, only what verify() says.
     */
    public function callback(Request $request, string $provider): RedirectResponse
    {
        $reference = $provider === 'flutterwave'
            ? $request->query('tx_ref')
            : ($request->query('reference') ?? $request->query('trxref'));

        $payment = Payment::query()->where('reference', $reference)->where('provider', $provider)->first();

        if (! $payment) {
            return to_route('student.dashboard')->with('status', 'We could not find that payment.');
        }

        if ($payment->isVerified()) {
            return to_route('student.dashboard')->with('status', 'Payment already confirmed — you are enrolled.');
        }

        $gateway = (new PaymentGatewayFactory())->make($provider);
        $result = $gateway->verify($reference);

        if (! $result['success']) {
            $payment->update(['status' => 'failed']);

            $failureRoute = $payment->order->cohort_id
                ? to_route('checkout.create', $payment->order->cohort)
                : to_route('student.cart.index');

            return $failureRoute->with('status', 'Payment was not successful. Please try again.');
        }

        try {
            (new ActivateEnrollmentFromPaymentAction())->activate($payment, (string) $result['provider_reference']);
        } catch (EnrollmentNotAllowedException $e) {
            // Payment succeeded but the cohort rules blocked enrollment (e.g. filled
            // up between checkout start and payment completion) — refund is a manual
            // admin step for now; see README "Known gaps".
            return to_route('student.dashboard')->with('status', 'Payment received, but enrollment could not be completed automatically: '.$e->getMessage().' Contact support.');
        }

        return to_route('student.dashboard')->with('status', 'Payment confirmed — you are enrolled!');
    }

    public function showBankTransfer(Order $order): Response
    {
        abort_unless($order->user_id === request()->user()->id, 403);

        return Inertia::render('Student/Checkout/BankTransfer', [
            'order' => ['id' => $order->id, 'reference' => $order->reference, 'formatted_amount' => $order->formattedAmount(), 'status' => $order->status],
            'bank' => [
                'bank_name' => PlatformSetting::get('payments', 'bank_name', 'Not configured'),
                'account_name' => PlatformSetting::get('payments', 'account_name', 'Not configured'),
                'account_number' => PlatformSetting::get('payments', 'account_number', 'Not configured'),
            ],
        ]);
    }

    private function enrollFree(Request $request, Cohort $cohort): RedirectResponse
    {
        try {
            (new EnrollStudentAction())->execute($request->user(), $cohort);
        } catch (EnrollmentNotAllowedException $e) {
            throw ValidationException::withMessages(['cohort' => $e->getMessage()]);
        }

        return to_route('student.dashboard')->with('status', "You're enrolled — welcome!");
    }
}
