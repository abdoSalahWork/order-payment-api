<?php

namespace Tests\Unit;

use App\Models\PaymentGatewaySetting;
use App\Services\PaymentGateways\CreditCardGateway;
use App\Services\PaymentGateways\PaymentGatewayFactory;
use App\Services\PaymentGateways\PayPalGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    private PaymentGatewayFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new PaymentGatewayFactory();

        // Run migrations before each test
        $this->artisan('migrate');

        // Create gateway settings
        PaymentGatewaySetting::create([
            'gateway_name' => 'credit_card',
            'is_active' => true,
            'credentials' => [
                'api_key' => 'test_api_key',
                'secret_key' => 'test_secret_key',
            ],
            'settings' => [
                'mode' => 'sandbox',
                'currency' => 'USD',
            ],
        ]);

        PaymentGatewaySetting::create([
            'gateway_name' => 'paypal',
            'is_active' => true,
            'credentials' => [
                'client_id' => 'test_client_id',
                'client_secret' => 'test_client_secret',
            ],
            'settings' => [
                'mode' => 'sandbox',
                'currency' => 'USD',
            ],
        ]);
    }

    public function test_can_create_credit_card_gateway(): void
    {
        $gateway = $this->factory->create('credit_card');
        $this->assertInstanceOf(CreditCardGateway::class, $gateway);
    }

    public function test_can_create_paypal_gateway(): void
    {
        $gateway = $this->factory->create('paypal');
        $this->assertInstanceOf(PayPalGateway::class, $gateway);
    }

    public function test_credit_card_gateway_validates_required_fields(): void
    {
        $gateway = $this->factory->create('credit_card');

        $validData = [
            'card_number' => '4111111111111111',
            'expiry_date' => '12/25',
            'cvv' => '123',
            'amount' => 100.00
        ];

        $invalidData = [
            'card_number' => '4111111111111111',
            'expiry_date' => '12/25'
            // Missing CVV and amount
        ];

        $this->assertTrue($gateway->validatePayment($validData));
        $this->assertFalse($gateway->validatePayment($invalidData));
    }

    public function test_paypal_gateway_validates_required_fields(): void
    {
        $gateway = $this->factory->create('paypal');

        $validData = [
            'email' => 'test@example.com',
            'amount' => 100.00
        ];

        $invalidData = [
            'email' => 'test@example.com'
            // Missing amount
        ];

        $this->assertTrue($gateway->validatePayment($validData));
        $this->assertFalse($gateway->validatePayment($invalidData));
    }

    public function test_credit_card_gateway_processes_payment(): void
    {
        $gateway = $this->factory->create('credit_card');

        $result = $gateway->processPayment([
            'card_number' => '4111111111111111',
            'expiry_date' => '12/25',
            'cvv' => '123',
            'amount' => 100.00
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('payment_id', $result);
    }

    public function test_paypal_gateway_processes_payment(): void
    {
        $gateway = $this->factory->create('paypal');

        $result = $gateway->processPayment([
            'email' => 'test@example.com',
            'amount' => 100.00
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('payment_id', $result);
    }
}