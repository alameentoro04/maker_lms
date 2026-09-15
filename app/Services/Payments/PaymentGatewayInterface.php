<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\Payment;

/**
 * Paystack and Flutterwave implement this identically shaped interface so
 * CheckoutController never branches on provider — it calls initialize() then
 * later verify(), and the provider-specific HTTP details stay isolated here.
 */
interface PaymentGatewayInterface
{
    /** @return array{authorization_url: string, reference: string} */
    public function initialize(Order $order, Payment $payment): array;

    /**
     * MUST hit the provider's API to confirm status — never trust a
     * frontend-reported "success" query param. See CheckoutController and
     * the webhook controllers, both of which call this before activating
     * anything.
     *
     * @return array{success: bool, amount: ?int, currency: ?string, provider_reference: ?string, raw: array}
     */
    public function verify(string $reference): array;
}
