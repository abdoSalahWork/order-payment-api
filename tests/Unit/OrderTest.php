<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_can_be_created(): void
    {
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'total_amount' => 100.00,
            'status' => 'pending',
            'items' => [
                [
                    'product_name' => 'Test Product',
                    'quantity' => 1,
                    'price' => 100.00
                ]
            ]
        ]);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals(100.00, $order->total_amount);
        $this->assertEquals('pending', $order->status);
    }

    public function test_order_cannot_be_deleted_with_payments(): void
    {
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'total_amount' => 100.00,
            'status' => 'confirmed',
            'items' => [
                [
                    'product_name' => 'Test Product',
                    'quantity' => 1,
                    'price' => 100.00
                ]
            ]
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_id' => 'PAY-123',
            'amount' => 100.00,
            'status' => 'successful',
            'payment_method' => 'credit_card',
            'payment_details' => []
        ]);

        $this->assertFalse($order->canBeDeleted());
    }

    public function test_order_can_be_deleted_without_payments(): void
    {
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'total_amount' => 100.00,
            'status' => 'pending',
            'items' => [
                [
                    'product_name' => 'Test Product',
                    'quantity' => 1,
                    'price' => 100.00
                ]
            ]
        ]);

        $this->assertTrue($order->canBeDeleted());
    }
}