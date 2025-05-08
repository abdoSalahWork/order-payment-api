<?php

namespace App\Services\PaymentGateways;

use App\Models\PaymentGatewaySetting;

class PaymentGatewayFactory
{
    private array $gateways = [
        'credit_card' => CreditCardGateway::class,
        'paypal' => PayPalGateway::class,
    ];

    public function create(string $gatewayName): PaymentGatewayInterface
    {
        if (!isset($this->gateways[$gatewayName])) {
            throw new \InvalidArgumentException("Payment gateway '{$gatewayName}' not found");
        }

        $config = PaymentGatewaySetting::getGatewayConfig($gatewayName);

        if (!$config) {
            throw new \InvalidArgumentException("Payment gateway '{$gatewayName}' is not configured or inactive");
        }

        $gatewayClass = $this->gateways[$gatewayName];
        return new $gatewayClass($config);
    }

    public function registerGateway(string $name, string $gatewayClass): void
    {
        if (!is_subclass_of($gatewayClass, PaymentGatewayInterface::class)) {
            throw new \InvalidArgumentException("Gateway class must implement PaymentGatewayInterface");
        }

        $this->gateways[$name] = $gatewayClass;
    }
}
