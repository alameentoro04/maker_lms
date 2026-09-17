<?php

namespace App\Http\Controllers\Student;

use App\Actions\ActivateEnrollmentFromPaymentAction;
use App\Actions\CreateOrderAction;
use App\Exceptions\EnrollmentNotAllowedException;
use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Payment;
use App\Services\CouponService;
use App\Services\Payments\PaymentGatewayFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CartCheckoutController extends Controller
{
    public function store(Request $request): RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        $validated = $request->validate([
            'method' => ['required', 'in:paystack,flutterwave,bank_transfer'],
            'coupon_code' => ['nullable', 'string', 'max:30'],
        ]);

        $user = $request->user();
        $cartItems = CartItem::query()->where('user_id', $user->id)->with(['cohort.course', 'package'])->get();
        abort_if($cartItems->isEmpty(), 422, 'Your cart is empty.');

        $coupon = null;
        if (! empty($validated['coupon_code'])) {
            $subtotal = $cartItems->sum(fn (CartItem $i) => $i->amount());
            $resolved = (new CouponService())->resolve($validated['coupon_code'], $user, $subtotal);

            if (! $resolved) {
                throw ValidationException::withMessages(['coupon_code' => 'That coupon code is invalid, expired, or already used.']);
            }

            $coupon = $resolved['coupon'];
        }

        $order = (new CreateOrderAction())->executeFromCart($user, $cartItems, $coupon);

        // Cart is cleared once the order exists — losing an in-progress
        // payment attempt is not the same as losing the cart; the order
        // itself now holds the items, and CartItem rows are just the
        // pre-checkout holding area.
        CartItem::query()->where('user_id', $user->id)->delete();

        if ($order->amount === 0) {
            return $this->activateFreeOrder($order);
        }

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

    private function activateFreeOrder(Order $order): RedirectResponse
    {
        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'provider' => 'manual',
            'reference' => 'PAY-'.strtoupper(Str::random(12)),
            'status' => 'pending',
            'amount' => 0,
            'currency' => $order->currency,
        ]);

        try {
            app(ActivateEnrollmentFromPaymentAction::class)->activate($payment, 'coupon-covered');
        } catch (EnrollmentNotAllowedException $e) {
            return to_route('student.cart.index')->with('status', 'Could not complete enrollment: '.$e->getMessage());
        }

        return to_route('student.dashboard')->with('status', "You're enrolled — welcome!");
    }
}
