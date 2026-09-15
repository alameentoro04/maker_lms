<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackGateway implements PaymentGatewayInterface
{
    private string $secretKey;
    private string $baseUrl = 'https://api.paystack.co';

    public function __construct()
    {
        $this->secretKey = (string) config('payment.paystack.secret_key');
    }

    public function initialize(Order $order, Payment $payment): array
    {
        abort_if(empty($this->secretKey), 500, 'Paystack is not configured (PAYSTACK_SECRET_KEY missing).');

        $response = Http::withToken($this->secretKey)
            ->post("{$this->baseUrl}/transaction/initialize", [
                'email' => $order->user->email,
                'amount' => $payment->amount, // Paystack expects kobo — our minor units already match for NGN
                'currency' => $payment->currency,
                'reference' => $payment->reference,
                'callback_url' => route('checkout.callback', 'paystack'),
                'metadata' => ['order_id' => $order->id, 'payment_id' => $payment->id],
            ]);

        if (! $response->successful() || ! $response->json('status')) {
            Log::error('Paystack initialize failed', ['response' => $response->json()]);
            abort(502, 'Could not start Paystack checkout. Please try again.');
        }

        return [
            'authorization_url' => $response->json('data.authorization_url'),
            'reference' => $response->json('data.reference'),
        ];
    }

    public function verify(string $reference): array
    {
        $response = Http::withToken($this->secretKey)
            ->get("{$this->baseUrl}/transaction/verify/{$reference}");

        $data = $response->json('data');
        $success = $response->successful() && ($data['status'] ?? null) === 'success';

        return [
            'success' => $success,
            'amount' => $data['amount'] ?? null,
            'currency' => $data['currency'] ?? null,
            'provider_reference' => $data['id'] ?? null,
            'raw' => $response->json() ?? [],
        ];
    }
}
