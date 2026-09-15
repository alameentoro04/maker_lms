<?php

namespace App\Http\Controllers\Webhooks;

use App\Actions\ActivateEnrollmentFromPaymentAction;
use App\Exceptions\EnrollmentNotAllowedException;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentWebhook;
use App\Services\Payments\PaymentGatewayFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class FlutterwaveWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $signature = $request->header('verif-hash');
        $secret = (string) config('payment.flutterwave.webhook_secret');

        $log = PaymentWebhook::query()->create(['provider' => 'flutterwave', 'payload' => $request->all()]);

        if (! $secret || ! hash_equals($secret, (string) $signature)) {
            $log->update(['processing_note' => 'Signature verification failed — ignored.']);

            return response('invalid signature', 401);
        }

        $reference = $request->input('data.tx_ref') ?? $request->input('txRef');
        $status = $request->input('data.status') ?? $request->input('status');

        if ($status !== 'successful' || ! $reference) {
            $log->update(['processed_at' => now(), 'processing_note' => "Ignored status: {$status}"]);

            return response('ok');
        }

        $payment = Payment::query()->where('reference', $reference)->where('provider', 'flutterwave')->first();

        if (! $payment) {
            $log->update(['processing_note' => "No matching payment for reference {$reference}"]);

            return response('ok');
        }

        try {
            $gateway = app(PaymentGatewayFactory::class)->make('flutterwave');
            $result = $gateway->verify($reference);

            if ($result['success']) {
                (new ActivateEnrollmentFromPaymentAction())->activate($payment, (string) $result['provider_reference']);
                $log->update(['processed_at' => now(), 'processing_note' => 'Activated']);
            } else {
                $log->update(['processed_at' => now(), 'processing_note' => 'Verify did not confirm success']);
            }
        } catch (EnrollmentNotAllowedException $e) {
            Log::warning('Flutterwave webhook: payment verified but enrollment blocked', ['payment_id' => $payment->id, 'reason' => $e->getMessage()]);
            $log->update(['processed_at' => now(), 'processing_note' => 'Enrollment blocked: '.$e->getMessage()]);
        }

        return response('ok');
    }
}
