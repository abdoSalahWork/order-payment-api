<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentGateways\PaymentGatewayFactory;
use Illuminate\Http\JsonResponse;

class PaymentService
{
    public function __construct(
        private PaymentGatewayFactory $gatewayFactory
    ) {}

    public function processPayment(array $paymentData, Order $order): array
    {
        $this->validateOrderStatus($order);

        $paymentGateway = $this->gatewayFactory->create($paymentData['payment_method']);
        $paymentResult = $this->processPaymentWithGateway($paymentGateway, $paymentData);

        if (!$paymentResult['success']) {
            return [
                'success' => false,
                'message' => 'Payment failed',
                'error' => $paymentResult['message'] ?? 'Unknown error occurred'
            ];
        }

        $payment = $this->createPaymentRecord($paymentResult, $paymentData, $order);
        $this->updateOrderStatus($order);

        return [
            'success' => true,
            'payment' => $payment,
            'order' => $order->fresh(),
            'total_paid' => $this->calculateTotalPaid($order),
            'remaining_amount' => $order->total_amount - $this->calculateTotalPaid($order)
        ];
    }

    private function validateOrderStatus(Order $order): void
    {
        $allowedStatuses = ['pending', 'partially_paid'];

        if (!in_array($order->status, $allowedStatuses)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Order cannot be paid. Current status: %s. Allowed statuses: %s',
                    $order->status,
                    implode(', ', $allowedStatuses)
                )
            );
        }

        // Check if order is already fully paid
        if ($this->calculateTotalPaid($order) >= $order->total_amount) {
            throw new \InvalidArgumentException('Order is already fully paid');
        }
    }

    private function processPaymentWithGateway($gateway, array $paymentData): array
    {
        return $gateway->processPayment([
            'amount' => $paymentData['payment_details']['amount'],
            'currency' => 'USD',
            'payment_details' => $paymentData['payment_details']
        ]);
    }

    private function createPaymentRecord(array $paymentResult, array $paymentData, Order $order): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'payment_id' => $paymentResult['payment_id'],
            'amount' => $paymentData['payment_details']['amount'],
            'currency' => 'USD',
            'payment_method' => $paymentData['payment_method'],
            'status' => 'successful',
            'transaction_id' => $paymentResult['transaction_id'] ?? null,
            'payment_details' => $paymentData['payment_details']
        ]);
    }

    private function calculateTotalPaid(Order $order): float
    {
        return $order->payments()
            ->where('status', 'successful')
            ->sum('amount');
    }

    private function updateOrderStatus(Order $order): void
    {
        $totalPaid = $this->calculateTotalPaid($order);

        $order->update([
            'status' => $totalPaid >= $order->total_amount ? 'paid' : 'partially_paid'
        ]);
    }
}
