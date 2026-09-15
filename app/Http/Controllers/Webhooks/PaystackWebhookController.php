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

class PaystackWebhookController extends Controller
{
    /**
     * The authoritative activation path in production — more reliable than
     * the browser callback redirect, which a student can close before it
     * completes. Every payload is logged to payment_webhooks regardless of
     * outcome, for auditing and replay if something goes wrong downstream.
     */
    public function __invoke(Request $request): Response
    {
        $signature = $request->header('x-paystack-signature');
        $secret = (string) config('payment.paystack.secret_key');
        $expected = hash_hmac('sha512', $request->getContent(), $secret);

        $log = PaymentWebhook::query()->create(['provider' => 'paystack', 'payload' => $request->all()]);

        if (! $secret || ! hash_equals($expected, (string) $signature)) {
            $log->update(['processing_note' => 'Signature verification failed — ignored.']);

            return response('invalid signature', 401);
        }

        $event = $request->input('event');
        $reference = $request->input('data.reference');

        if ($event !== 'charge.success' || ! $reference) {
            $log->update(['processed_at' => now(), 'processing_note' => "Ignored event: {$event}"]);

            return response('ok');
        }

        $payment = Payment::query()->where('reference', $reference)->where('provider', 'paystack')->first();

        if (! $payment) {
            $log->update(['processing_note' => "No matching payment for reference {$reference}"]);

            return response('ok'); // 200 so Paystack doesn't retry forever on an unrelated/old event
        }

        try {
            // Still re-verify against the API rather than trusting the webhook
            // payload's amount/status directly — belt and braces against a
            // forged or malformed payload getting this far.
            $gateway = app(PaymentGatewayFactory::class)->make('paystack');
            $result = $gateway->verify($reference);

            if ($result['success']) {
                (new ActivateEnrollmentFromPaymentAction())->activate($payment, (string) $result['provider_reference']);
                $log->update(['processed_at' => now(), 'processing_note' => 'Activated']);
            } else {
                $log->update(['processed_at' => now(), 'processing_note' => 'Verify did not confirm success']);
            }
        } catch (EnrollmentNotAllowedException $e) {
            Log::warning('Paystack webhook: payment verified but enrollment blocked', ['payment_id' => $payment->id, 'reason' => $e->getMessage()]);
            $log->update(['processed_at' => now(), 'processing_note' => 'Enrollment blocked: '.$e->getMessage()]);
        }

        return response('ok');
    }
}
