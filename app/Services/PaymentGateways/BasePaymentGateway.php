<?php

namespace App\Services\PaymentGateways;

use App\Models\PaymentGatewaySetting;
use InvalidArgumentException;

abstract class BasePaymentGateway implements PaymentGatewayInterface
{
    protected array $settings;
    protected bool $isActive;

    public function __construct()
    {
        $this->loadSettings();
    }

    protected function loadSettings(): void
    {
        $setting = PaymentGatewaySetting::where('gateway_name', $this->getGatewayName())
            ->where('is_active', true)
            ->first();

        if (!$setting) {
            throw new InvalidArgumentException("Payment gateway '{$this->getGatewayName()}' is not configured or inactive");
        }

        $this->settings = $setting->settings;
        $this->isActive = $setting->is_active;
    }

    protected function validateRequiredFields(array $paymentData, array $requiredFields): bool
    {

        foreach ($requiredFields as $field) {
            if (!isset($paymentData[$field]) || empty($paymentData[$field])) {
                return false;
            }
        }
        return true;
    }

    protected function validateAmount(float $amount): bool
    {
        $minAmount = $this->settings['min_amount'] ?? 0.01;
        $maxAmount = $this->settings['max_amount'] ?? 10000.00;

        return $amount >= $minAmount && $amount <= $maxAmount;
    }

    protected function validateCurrency(string $currency): bool
    {
        $supportedCurrencies = $this->settings['supported_currencies'] ?? ['USD'];
        return in_array($currency, $supportedCurrencies);
    }

    protected function generatePaymentId(): string
    {
        return uniqid('PAY-' . strtoupper($this->getGatewayName()) . '-', true);
    }

    abstract public function getGatewayName(): string;
}