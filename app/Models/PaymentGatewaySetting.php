<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGatewaySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'gateway_name',
        'is_active',
        'credentials',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'credentials' => 'encrypted:array',
        'settings' => 'array',
    ];

    public static function getGatewayConfig(string $gatewayName): ?array
    {
        $setting = self::where('gateway_name', $gatewayName)
            ->where('is_active', true)
            ->first();

        if (!$setting) {
            return null;
        }

        return [
            'credentials' => $setting->credentials,
            'settings' => $setting->settings,
        ];
    }
}