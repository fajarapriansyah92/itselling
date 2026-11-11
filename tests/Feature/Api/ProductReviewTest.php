<?php

namespace Tests\Feature\Api;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_review_if_order_not_completed(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::Paid,
        ]);
        $item = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson(route('api.v1.orders.reviews.store', $order), [
            'order_item_id' => $item->id,
            'rating' => 5,
            'comment' => 'Barang sangat bagus!',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Pesanan belum selesai. Anda hanya dapat memberi ulasan setelah pesanan berstatus Selesai.');
    }

    public function test_user_cannot_review_other_users_order(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user1->id,
            'status' => OrderStatus::Completed,
        ]);
        $item = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
        ]);

        Sanctum::actingAs($user2);

        $response = $this->postJson(route('api.v1.orders.reviews.store', $order), [
            'order_item_id' => $item->id,
            'rating' => 5,
            'comment' => 'Hacker comment',
        ]);

        $response->assertForbidden();
    }

    public function test_user_can_review_completed_order_item(): void
    {
        $user = User::factory()->create(['name' => 'Fajar Pembeli']);
        $product = Product::factory()->create(['name' => 'MikroTik Router']);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::Completed,
        ]);
        $item = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson(route('api.v1.orders.reviews.store', $order), [
            'order_item_id' => $item->id,
            'rating' => 5,
            'comment' => 'Pengiriman super cepat dan router berfungsi normal!',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.rating', 5)
            ->assertJsonPath('data.comment', 'Pengiriman super cepat dan router berfungsi normal!')
            ->assertJsonPath('data.user_name', 'Fajar Pembeli');

        $this->assertDatabaseHas('product_reviews', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'order_item_id' => $item->id,
            'user_id' => $user->id,
            'rating' => 5,
            'comment' => 'Pengiriman super cepat dan router berfungsi normal!',
        ]);
    }

    public function test_public_can_fetch_product_reviews_and_summary(): void
    {
        $product = Product::factory()->create();
        $user1 = User::factory()->create(['name' => 'User One']);
        $user2 = User::factory()->create(['name' => 'User Two']);

        ProductReview::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user1->id,
            'rating' => 5,
            'comment' => 'Bagus sekali',
        ]);

        ProductReview::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user2->id,
            'rating' => 4,
            'comment' => 'Cukup memuaskan',
        ]);

        $response = $this->getJson(route('api.v1.products.reviews.index', $product));

        $response->assertOk()
            ->assertJsonPath('summary.rating_average', 4.5)
            ->assertJsonPath('summary.reviews_count', 2)
            ->assertJsonPath('summary.breakdown.5', 1)
            ->assertJsonPath('summary.breakdown.4', 1)
            ->assertJsonCount(2, 'data');
    }

    public function test_admin_can_reply_to_review(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $review = ProductReview::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.reviews.reply', $review), [
            'admin_reply' => 'Terima kasih telah berbelanja di toko kami!',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('product_reviews', [
            'id' => $review->id,
            'admin_reply' => 'Terima kasih telah berbelanja di toko kami!',
        ]);
    }

    public function test_can_fetch_product_reviews_using_slug_string(): void
    {
        $product = Product::factory()->create(['slug' => 'test-product-slug']);
        ProductReview::factory()->create([
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Review dengan slug!',
        ]);

        $response = $this->getJson('/api/v1/products/test-product-slug/reviews');

        $response->assertOk()
            ->assertJsonPath('summary.reviews_count', 1)
            ->assertJsonPath('data.0.comment', 'Review dengan slug!');
    }
}
