<?php

namespace Tests\Feature\Api;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_generate_snap_token_for_their_order(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'MikroTik RB750Gr3',
            'price' => 850000,
        ]);

        $order = Order::create([
            'order_number' => 'INV-TEST-001',
            'user_id' => $user->id,
            'status' => OrderStatus::Pending,
            'total' => 850000,
            'recipient_name' => 'John Doe',
            'recipient_phone' => '08123456789',
            'shipping_address' => 'Jl. Test No. 123',
            'payment_method' => 'midtrans',
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => 850000,
            'quantity' => 1,
            'subtotal' => 850000,
        ]);

        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => 'dummy-snap-token-12345',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v4/redirection/dummy-snap-token-12345',
            ], 201),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson(route('api.v1.orders.snap-token', $order));

        $response->assertOk()
            ->assertJsonPath('token', 'dummy-snap-token-12345')
            ->assertJsonPath('redirect_url', 'https://app.sandbox.midtrans.com/snap/v4/redirection/dummy-snap-token-12345')
            ->assertJsonPath('order.order_number', 'INV-TEST-001');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'snap_token' => 'dummy-snap-token-12345',
            'snap_redirect_url' => 'https://app.sandbox.midtrans.com/snap/v4/redirection/dummy-snap-token-12345',
        ]);
    }

    public function test_snap_token_locks_to_selected_payment_channel_like_bca(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'MikroTik RB750Gr3',
            'price' => 850000,
        ]);

        $order = Order::create([
            'order_number' => 'INV-TEST-BCA',
            'user_id' => $user->id,
            'status' => OrderStatus::Pending,
            'total' => 850000,
            'recipient_name' => 'John Doe',
            'recipient_phone' => '08123456789',
            'shipping_address' => 'Jl. Test No. 123',
            'payment_method' => 'bank_transfer',
            'payment_channel' => 'BCA',
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => 850000,
            'quantity' => 1,
            'subtotal' => 850000,
        ]);

        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => function ($request) {
                $this->assertEquals(['bca_va'], $request['enabled_payments']);

                return Http::response([
                    'token' => 'dummy-bca-token',
                    'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v4/redirection/dummy-bca-token',
                ], 201);
            },
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson(route('api.v1.orders.snap-token', $order));

        $response->assertOk()
            ->assertJsonPath('token', 'dummy-bca-token');
    }

    public function test_midtrans_webhook_marks_order_as_paid_when_settlement(): void
    {
        config(['midtrans.server_key' => 'test-server-key']);

        $user = User::factory()->create();
        $order = Order::create([
            'order_number' => 'INV-TEST-002',
            'user_id' => $user->id,
            'status' => OrderStatus::Pending,
            'total' => 500000,
            'recipient_name' => 'Alice',
            'recipient_phone' => '08123456780',
            'shipping_address' => 'Jl. Test No. 456',
            'payment_method' => 'midtrans',
        ]);

        $orderId = 'INV-TEST-002';
        $statusCode = '200';
        $grossAmount = '500000.00';
        $signatureKey = hash('sha512', $orderId.$statusCode.$grossAmount.'test-server-key');

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signatureKey,
            'transaction_status' => 'settlement',
            'payment_type' => 'bank_transfer',
            'va_numbers' => [
                [
                    'bank' => 'bca',
                    'va_number' => '123456789012',
                ],
            ],
        ];

        $response = $this->postJson(route('api.v1.midtrans.webhook'), $payload);

        $response->assertOk();

        $order->refresh();
        $this->assertEquals(OrderStatus::Paid, $order->status);
        $this->assertNotNull($order->paid_at);
        $this->assertEquals('123456789012', $order->va_number);
        $this->assertEquals('BCA', $order->payment_bank);

        $this->assertDatabaseHas('admin_notifications', [
            'type' => 'order_payment',
        ]);
    }

    public function test_customer_can_sync_payment_status_from_midtrans_api(): void
    {
        config(['midtrans.server_key' => 'test-server-key']);

        $user = User::factory()->create();
        $order = Order::create([
            'order_number' => 'INV-TEST-003',
            'user_id' => $user->id,
            'status' => OrderStatus::Pending,
            'total' => 750000,
            'recipient_name' => 'Bob',
            'recipient_phone' => '08123456781',
            'shipping_address' => 'Jl. Test No. 789',
            'payment_method' => 'midtrans',
        ]);

        Http::fake([
            'https://api.sandbox.midtrans.com/v2/INV-TEST-003/status' => Http::response([
                'order_id' => 'INV-TEST-003',
                'transaction_status' => 'settlement',
                'payment_type' => 'gopay',
                'gross_amount' => '750000.00',
            ], 200),
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.v1.orders.sync-payment', $order));

        $response->assertOk()
            ->assertJsonPath('status', 'paid')
            ->assertJsonPath('is_paid', true)
            ->assertJsonPath('order.order_number', 'INV-TEST-003');

        $order->refresh();
        $this->assertEquals(OrderStatus::Paid, $order->status);
    }

    public function test_simulator_can_mock_pay_order_successfully(): void
    {
        $user = User::factory()->create();
        $order = Order::create([
            'order_number' => 'INV-TEST-004',
            'user_id' => $user->id,
            'status' => OrderStatus::Pending,
            'total' => 1250000,
            'recipient_name' => 'Charlie',
            'recipient_phone' => '08123456782',
            'shipping_address' => 'Jl. Simulator No. 1',
            'payment_method' => 'midtrans',
            'va_number' => '987654321012',
            'payment_bank' => 'BCA',
        ]);

        $response = $this->postJson(route('api.v1.midtrans.mock-pay'), [
            'order_number' => 'INV-TEST-004',
            'va_number' => '987654321012',
            'bank' => 'bca',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 'paid')
            ->assertJsonPath('order_number', 'INV-TEST-004');

        $order->refresh();
        $this->assertEquals(OrderStatus::Paid, $order->status);
        $this->assertNotNull($order->paid_at);
    }
}
