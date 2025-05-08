<?php

namespace App\Services\PaymentGateways;

class CreditCardGateway extends BasePaymentGateway
{
    public function processPayment(array $paymentData): array
    {

        if (!$this->validatePayment($paymentData['payment_details'])) {
            return [
                'success' => false,
                'message' => 'Invalid payment data',
                'payment_id' => null,
            ];
        }
        // Simulate payment processing
        $paymentId = $this->generatePaymentId();

        // In a real implementation, this would make an API call to the payment processor
        $success = rand(0, 1) === 1;
        return [
            'success' => $success,
            'message' => $success ? 'Payment processed successfully' : 'Payment failed',
            'payment_id' => $paymentId,
        ];
    }

    public function getGatewayName(): string
    {
        return 'credit_card';
    }

    public function validatePayment(array $paymentData): bool
    {
        $requiredFields = ['card_number', 'expiry_date', 'cvv', 'amount'];
        return $this->validateRequiredFields($paymentData, $requiredFields);
    }
}