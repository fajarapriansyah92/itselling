<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductInquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_send_product_inquiry_and_trigger_admin_notification(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Windows 11 Pro OEM',
            'slug' => 'windows-11-pro-oem',
            'sku' => 'LIC-002',
            'price' => 2650000,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson(route('api.v1.products.messages.store', $product->slug), [
            'message' => 'Halo admin, lisensi ini bisa dipindahkan ke laptop lain tidak ya?',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.message', 'Halo admin, lisensi ini bisa dipindahkan ke laptop lain tidak ya?')
            ->assertJsonPath('data.sender_type', 'customer')
            ->assertJsonPath('data.is_me', true);

        $this->assertDatabaseHas('product_inquiries', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->assertDatabaseHas('product_inquiry_messages', [
            'user_id' => $user->id,
            'sender_type' => 'customer',
            'message' => 'Halo admin, lisensi ini bisa dipindahkan ke laptop lain tidak ya?',
        ]);

        $this->assertDatabaseHas('admin_notifications', [
            'type' => 'order_chat',
            'title' => 'Tanya Produk: Windows 11 Pro OEM',
        ]);
    }

    public function test_admin_can_view_product_chat_and_reply(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'MikroTik hEX RB750Gr3',
        ]);

        $inquiry = ProductInquiry::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $inquiry->messages()->create([
            'user_id' => $user->id,
            'sender_type' => 'customer',
            'message' => 'Apakah ini sudah termasuk adaptor daya?',
        ]);

        // Admin opens the chat room
        $this->actingAs($admin)
            ->get(route('admin.chats.product.show', $inquiry))
            ->assertOk()
            ->assertSee('MikroTik hEX RB750Gr3')
            ->assertSee('Apakah ini sudah termasuk adaptor daya?');

        // Admin replies
        $this->actingAs($admin)
            ->post(route('admin.chats.product.reply', $inquiry), [
                'message' => 'Halo kak, iya sudah include adaptor 24V resmi bawaan pabrik ya.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('product_inquiry_messages', [
            'user_id' => $admin->id,
            'sender_type' => 'admin',
            'message' => 'Halo kak, iya sudah include adaptor 24V resmi bawaan pabrik ya.',
        ]);
    }

    public function test_admin_chats_index_returns_realtime_json_payload(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Switch Cisco Catalyst',
        ]);

        $inquiry = ProductInquiry::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $inquiry->messages()->create([
            'user_id' => $user->id,
            'sender_type' => 'customer',
            'message' => 'Apakah port SFP sudah include transceiver?',
        ]);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.chats.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'total_unread',
                'total_unread_products',
                'total_unread_orders',
                'product_inquiries_total',
                'orders_total',
                'product_inquiries',
                'orders',
            ])
            ->assertJsonPath('total_unread_products', 1)
            ->assertJsonPath('product_inquiries.0.product_name', 'Switch Cisco Catalyst')
            ->assertJsonPath('product_inquiries.0.unread_count', 1);
    }

    public function test_customer_can_fetch_their_product_inquiries_and_unread_count(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Fortinet FortiGate 60F',
            'slug' => 'fortinet-fortigate-60f',
            'sku' => 'SEC-001',
            'price' => 12500000,
        ]);

        $inquiry = ProductInquiry::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $inquiry->messages()->create([
            'user_id' => $user->id,
            'sender_type' => 'customer',
            'message' => 'Apakah termasuk bundle lisensi UTM 1 tahun?',
        ]);

        $inquiry->messages()->create([
            'user_id' => $admin->id,
            'sender_type' => 'admin',
            'message' => 'Halo kak, iya betul sudah termasuk bundle UTP/UTM 1 tahun resmi.',
            'read_at' => null,
        ]);

        Sanctum::actingAs($user);

        // Test unread count endpoint
        $unreadResponse = $this->getJson(route('api.v1.user.inquiries.unread-count'));
        $unreadResponse->assertOk()
            ->assertJsonPath('unread_count', 1);

        // Test inquiries list endpoint
        $inquiriesResponse = $this->getJson(route('api.v1.user.inquiries.index'));
        $inquiriesResponse->assertOk()
            ->assertJsonPath('total_unread', 1)
            ->assertJsonPath('data.0.id', $inquiry->id)
            ->assertJsonPath('data.0.unread_count', 1)
            ->assertJsonPath('data.0.product.name', 'Fortinet FortiGate 60F')
            ->assertJsonPath('data.0.product.sku', 'SEC-001')
            ->assertJsonPath('data.0.latest_message.message', 'Halo kak, iya betul sudah termasuk bundle UTP/UTM 1 tahun resmi.')
            ->assertJsonPath('data.0.latest_message.sender_type', 'admin')
            ->assertJsonPath('data.0.latest_message.is_me', false);
    }
}
