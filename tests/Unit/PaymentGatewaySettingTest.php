<?php

namespace Tests\Unit;

use App\Models\PaymentGatewaySetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PaymentGatewaySettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_payment_gateway_setting(): void
    {
        $setting = PaymentGatewaySetting::create([
            'gateway_name' => 'credit_card',
            'is_active' => true,
            'credentials' => [
                'api_key' => 'test_api_key',
                'secret_key' => 'test_secret_key',
            ],
            'settings' => [
                'mode' => 'sandbox',
                'currency' => 'USD',
                'timeout' => 30,
            ],
        ]);

        $this->assertInstanceOf(PaymentGatewaySetting::class, $setting);
        $this->assertEquals('credit_card', $setting->gateway_name);
        $this->assertTrue($setting->is_active);
        $this->assertEquals('test_api_key', $setting->credentials['api_key']);
        $this->assertEquals('sandbox', $setting->settings['mode']);
    }

    public function test_can_get_gateway_config(): void
    {
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

        $config = PaymentGatewaySetting::getGatewayConfig('credit_card');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('credentials', $config);
        $this->assertArrayHasKey('settings', $config);
        $this->assertEquals('test_api_key', $config['credentials']['api_key']);
        $this->assertEquals('sandbox', $config['settings']['mode']);
    }

    public function test_returns_null_for_inactive_gateway(): void
    {
        PaymentGatewaySetting::create([
            'gateway_name' => 'credit_card',
            'is_active' => false,
            'credentials' => [
                'api_key' => 'test_api_key',
            ],
            'settings' => [
                'mode' => 'sandbox',
            ],
        ]);

        $config = PaymentGatewaySetting::getGatewayConfig('credit_card');

        $this->assertNull($config);
    }

    public function test_returns_null_for_nonexistent_gateway(): void
    {
        $config = PaymentGatewaySetting::getGatewayConfig('nonexistent_gateway');

        $this->assertNull($config);
    }

    public function test_credentials_are_encrypted(): void
    {
        $setting = PaymentGatewaySetting::create([
            'gateway_name' => 'credit_card',
            'is_active' => true,
            'credentials' => [
                'api_key' => 'sensitive_data',
                'secret_key' => 'very_sensitive_data',
            ],
            'settings' => [
                'mode' => 'sandbox',
            ],
        ]);

        // Get the raw database record
        $rawSetting = DB::table('payment_gateway_settings')
            ->where('id', $setting->id)
            ->first();

        // Check that the credentials are encrypted in the database
        $this->assertNotEquals(
            json_encode(['api_key' => 'sensitive_data', 'secret_key' => 'very_sensitive_data']),
            $rawSetting->credentials
        );

        // Check that we can still access the decrypted data
        $this->assertEquals('sensitive_data', $setting->credentials['api_key']);
        $this->assertEquals('very_sensitive_data', $setting->credentials['secret_key']);
    }
}
