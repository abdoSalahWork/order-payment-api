<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcessPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|in:credit_card,paypal',
            'payment_details' => 'required|array',
            'payment_details.card_number' => 'required_if:payment_method,credit_card|string|size:16',
            'payment_details.expiry_date' => 'required_if:payment_method,credit_card|string|regex:/^\d{2}\/\d{2}$/',
            'payment_details.cvv' => 'required_if:payment_method,credit_card|string|size:3',
            'payment_details.amount' => 'required|numeric|min:0.01',
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.required' => 'The order ID is required.',
            'order_id.exists' => 'The selected order does not exist.',
            'payment_method.required' => 'The payment method is required.',
            'payment_method.in' => 'The payment method must be either credit card or PayPal.',
            'payment_details.required' => 'Payment details are required.',
            'payment_details.card_number.required_if' => 'Card number is required for credit card payments.',
            'payment_details.card_number.size' => 'Card number must be 16 digits.',
            'payment_details.expiry_date.required_if' => 'Expiry date is required for credit card payments.',
            'payment_details.expiry_date.regex' => 'Expiry date must be in MM/YY format.',
            'payment_details.cvv.required_if' => 'CVV is required for credit card payments.',
            'payment_details.cvv.size' => 'CVV must be 3 digits.',
            'payment_details.amount.required' => 'Amount is required.',
            'payment_details.amount.numeric' => 'Amount must be a number.',
            'payment_details.amount.min' => 'Amount must be at least 0.01.',
        ];
    }
}
