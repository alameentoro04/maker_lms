<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlutterwaveGateway implements PaymentGatewayInterface
{
    private string $secretKey;
    private string $baseUrl = 'https://api.flutterwave.com/v3';

    public function __construct()
    {
        $this->secretKey = (string) config('payment.flutterwave.secret_key');
    }

    public function initialize(Order $order, Payment $payment): array
    {
        abort_if(empty($this->secretKey), 500, 'Flutterwave is not configured (FLUTTERWAVE_SECRET_KEY missing).');

        $response = Http::withToken($this->secretKey)
            ->post("{$this->baseUrl}/payments", [
                'tx_ref' => $payment->reference,
                'amount' => $payment->amount / 100, // Flutterwave expects a major-unit decimal amount
                'currency' => $payment->currency,
                'redirect_url' => route('checkout.callback', 'flutterwave'),
                'customer' => ['email' => $order->user->email, 'name' => $order->user->name],
                'meta' => ['order_id' => $order->id, 'payment_id' => $payment->id],
            ]);

        if (! $response->successful() || $response->json('status') !== 'success') {
            Log::error('Flutterwave initialize failed', ['response' => $response->json()]);
            abort(502, 'Could not start Flutterwave checkout. Please try again.');
        }

        return [
            'authorization_url' => $response->json('data.link'),
            'reference' => $payment->reference,
        ];
    }

    public function verify(string $reference): array
    {
        // Flutterwave verifies by their internal transaction id, not our tx_ref —
        // callers pass whichever they have; we resolve tx_ref -> id first if needed.
        $response = Http::withToken($this->secretKey)
            ->get("{$this->baseUrl}/transactions/verify_by_reference", ['tx_ref' => $reference]);

        $data = $response->json('data');
        $success = $response->successful() && ($data['status'] ?? null) === 'successful';

        return [
            'success' => $success,
            'amount' => isset($data['amount']) ? (int) round($data['amount'] * 100) : null,
            'currency' => $data['currency'] ?? null,
            'provider_reference' => $data['id'] ?? null,
            'raw' => $response->json() ?? [],
        ];
    }
}
