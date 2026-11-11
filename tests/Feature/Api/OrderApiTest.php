<?php

namespace Tests\Feature\Api;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function shippingData(): array
    {
        return [
            'recipient_name' => 'Budi',
            'recipient_phone' => '081200000000',
            'shipping_address' => 'Jl. Merdeka No. 1, Jakarta',
        ];
    }

    public function test_customer_can_place_an_order_and_stock_is_reduced(): void
    {
        $customer = Sanctum::actingAs(User::factory()->create());
        $router = Product::factory()->create(['price' => 1000000, 'stock' => 10]);
        $service = Product::factory()->service()->create(['price' => 2500000]);

        $response = $this->postJson('/api/v1/orders', [
            ...$this->shippingData(),
            'items' => [
                ['product_id' => $router->id, 'quantity' => 3],
                ['product_id' => $service->id, 'quantity' => 1],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.total', 5500000)
            ->assertJsonCount(2, 'data.items');

        $this->assertSame(7, $router->fresh()->stock);
        $this->assertNull($service->fresh()->stock);
        $this->assertDatabaseHas('orders', ['user_id' => $customer->id, 'total' => 5500000]);
    }

    public function test_order_is_rejected_when_stock_is_insufficient(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $product = Product::factory()->create(['stock' => 2]);

        $this->postJson('/api/v1/orders', [
            ...$this->shippingData(),
            'items' => [['product_id' => $product->id, 'quantity' => 5]],
        ])->assertUnprocessable()->assertJsonValidationErrors('items');

        $this->assertSame(2, $product->fresh()->stock);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_customer_only_sees_their_own_orders(): void
    {
        $customer = Sanctum::actingAs(User::factory()->create());
        Order::factory()->for($customer)->count(2)->create();
        $otherOrder = Order::factory()->create();

        $this->getJson('/api/v1/orders')->assertOk()->assertJsonCount(2, 'data');
        $this->getJson("/api/v1/orders/{$otherOrder->order_number}")->assertForbidden();
    }

    public function test_customer_can_upload_payment_proof_for_pending_order(): void
    {
        Storage::fake('public');
        $customer = Sanctum::actingAs(User::factory()->create());
        $order = Order::factory()->for($customer)->create();

        $this->postJson("/api/v1/orders/{$order->order_number}/payment-proof", [
            'payment_proof' => UploadedFile::fake()->create('bukti.jpg', 100, 'image/jpeg'),
        ])->assertOk();

        Storage::disk('public')->assertExists($order->fresh()->payment_proof_path);
    }

    public function test_payment_proof_cannot_be_uploaded_after_order_is_paid(): void
    {
        Storage::fake('public');
        $customer = Sanctum::actingAs(User::factory()->create());
        $order = Order::factory()->for($customer)->status(OrderStatus::Paid)->create();

        $this->postJson("/api/v1/orders/{$order->order_number}/payment-proof", [
            'payment_proof' => UploadedFile::fake()->create('bukti.jpg', 100, 'image/jpeg'),
        ])->assertForbidden();
    }

    public function test_cancelling_pending_order_restores_stock(): void
    {
        $customer = Sanctum::actingAs(User::factory()->create());
        $product = Product::factory()->create(['stock' => 4]);
        $order = Order::factory()->for($customer)->create();
        OrderItem::factory()->for($order)->for($product)->create(['quantity' => 3]);

        $this->postJson("/api/v1/orders/{$order->order_number}/cancel")
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        $this->assertSame(7, $product->fresh()->stock);
    }

    public function test_paid_order_cannot_be_cancelled_by_customer(): void
    {
        $customer = Sanctum::actingAs(User::factory()->create());
        $order = Order::factory()->for($customer)->status(OrderStatus::Paid)->create();

        $this->postJson("/api/v1/orders/{$order->order_number}/cancel")->assertForbidden();
    }

    public function test_customer_can_place_credit_card_order_and_is_automatically_paid_with_license_generated(): void
    {
        $customer = Sanctum::actingAs(User::factory()->create());
        $software = Product::factory()->software()->create(['price' => 500000]);

        $response = $this->postJson('/api/v1/orders', [
            ...$this->shippingData(),
            'payment_method' => 'credit_card',
            'payment_channel' => 'Kartu Kredit',
            'items' => [
                ['product_id' => $software->id, 'quantity' => 1],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'paid')
            ->assertJsonPath('data.payment_method', 'credit_card')
            ->assertJsonPath('data.payment_channel', 'Kartu Kredit');

        $order = Order::query()->where('user_id', $customer->id)->firstOrFail();
        $this->assertSame(OrderStatus::Paid, $order->status);
        $this->assertNotNull($order->paid_at);
        $this->assertNotNull($order->items()->first()->license_key);
    }
}
