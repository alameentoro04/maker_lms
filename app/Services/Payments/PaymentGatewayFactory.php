<?php

namespace App\Services\Payments;

use InvalidArgumentException;

class PaymentGatewayFactory
{
    public function make(string $provider): PaymentGatewayInterface
    {
        return match ($provider) {
            'paystack' => app(PaystackGateway::class),
            'flutterwave' => app(FlutterwaveGateway::class),
            default => throw new InvalidArgumentException("No online gateway for provider [{$provider}]. Bank transfer/manual don't use a gateway."),
        };
    }
}
