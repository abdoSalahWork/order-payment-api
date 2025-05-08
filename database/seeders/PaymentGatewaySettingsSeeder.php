<?php

namespace Database\Seeders;

use App\Models\PaymentGatewaySetting;
use Illuminate\Database\Seeder;

class PaymentGatewaySettingsSeeder extends Seeder
{
    public function run(): void
    {
        // Credit Card Gateway Settings
        PaymentGatewaySetting::create([
            'gateway_name' => 'credit_card',
            'is_active' => true,
            'settings' => [
                'api_key' => 'test_api_key',
                'api_secret' => 'test_api_secret',
                'test_mode' => true,
                'supported_currencies' => ['USD', 'EUR', 'GBP'],
                'min_amount' => 0.01,
                'max_amount' => 10000.00
            ]
        ]);

        // PayPal Gateway Settings
        PaymentGatewaySetting::create([
            'gateway_name' => 'paypal',
            'is_active' => true,
            'settings' => [
                'client_id' => 'test_client_id',
                'client_secret' => 'test_client_secret',
                'test_mode' => true,
                'supported_currencies' => ['USD', 'EUR', 'GBP'],
                'min_amount' => 0.01,
                'max_amount' => 10000.00
            ]
        ]);
    }
} 