<?php

namespace App\Services\PaymentGateways;

class PayPalGateway extends BasePaymentGateway
{
    public function processPayment(array $paymentData): array
    {
        if (!$this->validatePayment($paymentData)) {
            return [
                'success' => false,
                'message' => 'Invalid payment data',
                'payment_id' => null,
            ];
        }

        // Simulate PayPal payment processing
        $paymentId = $this->generatePaymentId();

        // In a real implementation, this would make an API call to PayPal
        $success = rand(0, 1) === 1;

        return [
            'success' => $success,
            'message' => $success ? 'PayPal payment processed successfully' : 'PayPal payment failed',
            'payment_id' => $paymentId,
        ];
    }

    public function getGatewayName(): string
    {
        return 'paypal';
    }

    public function validatePayment(array $paymentData): bool
    {
        $requiredFields = ['email', 'amount'];
        return $this->validateRequiredFields($paymentData, $requiredFields);
    }
}
