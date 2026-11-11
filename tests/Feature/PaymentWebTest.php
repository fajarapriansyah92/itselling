<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\ProductType;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_examiner_can_view_payment_web_page_with_prefilled_va(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'MikroTik Cloud Router',
            'price' => 1250000,
        ]);

        $order = Order::create([
            'order_number' => 'INV-TEST-WEB-001',
            'user_id' => $user->id,
            'status' => OrderStatus::Pending,
            'total' => 1250000,
            'recipient_name' => 'Dr. Penguji',
            'recipient_phone' => '08123456789',
            'shipping_address' => 'Kampus Ruang Sidang',
            'payment_method' => 'bank_transfer',
            'payment_channel' => 'BCA',
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => 1250000,
            'quantity' => 1,
            'subtotal' => 1250000,
        ]);

        $response = $this->get(route('payment.web', $order));

        $response->assertOk()
            ->assertSee('INV-TEST-WEB-001')
            ->assertSee('Dr. Penguji')
            ->assertSee('Rp 1.250.000')
            ->assertSee('12345');
    }

    public function test_examiner_can_click_pay_and_order_becomes_paid(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Antivirus Pro License',
            'price' => 350000,
            'type' => ProductType::Software,
        ]);

        $order = Order::create([
            'order_number' => 'INV-TEST-WEB-002',
            'user_id' => $user->id,
            'status' => OrderStatus::Pending,
            'total' => 350000,
            'recipient_name' => 'Dr. Penguji',
            'recipient_phone' => '08123456789',
            'shipping_address' => 'Kampus Ruang Sidang',
            'payment_method' => 'bank_transfer',
            'payment_channel' => 'BCA',
            'va_number' => '123458123456',
        ]);

        $item = $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => 350000,
            'quantity' => 1,
            'subtotal' => 350000,
        ]);

        $response = $this->postJson(route('payment.web.pay', $order), [
            'va_number' => '123458123456',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $order->refresh();
        $this->assertEquals(OrderStatus::Paid, $order->status);
        $this->assertNotNull($order->paid_at);

        $item->refresh();
        $this->assertNotNull($item->license_key);
    }
}
