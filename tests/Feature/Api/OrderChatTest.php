<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_send_message_and_trigger_admin_notification(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->postJson(route('api.v1.orders.messages.store', $order), [
            'message' => 'Halo min, kapan pesanan hardware saya dikirim ya?',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.message', 'Halo min, kapan pesanan hardware saya dikirim ya?')
            ->assertJsonPath('data.sender_type', 'customer')
            ->assertJsonPath('data.is_me', true);

        $this->assertDatabaseHas('order_messages', [
            'order_id' => $order->id,
            'user_id' => $user->id,
            'sender_type' => 'customer',
            'message' => 'Halo min, kapan pesanan hardware saya dikirim ya?',
        ]);

        $this->assertDatabaseHas('admin_notifications', [
            'type' => 'order_chat',
        ]);
    }

    public function test_customer_can_fetch_messages_and_mark_admin_replies_as_read(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $order = Order::factory()->create(['user_id' => $user->id]);

        $order->messages()->create([
            'user_id' => $user->id,
            'sender_type' => 'customer',
            'message' => 'Tolong info resi dong min',
        ]);

        $adminMsg = $order->messages()->create([
            'user_id' => $admin->id,
            'sender_type' => 'admin',
            'message' => 'Halo kak, resinya JNE12345678 ya.',
            'read_at' => null,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.v1.orders.messages.index', $order));

        $response->assertOk()
            ->assertJsonCount(2, 'data');

        $this->assertNotNull($adminMsg->fresh()->read_at);
    }

    public function test_admin_can_send_reply_via_web(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $customer->id]);

        $response = $this->actingAs($admin)->post(route('admin.orders.messages.store', $order), [
            'message' => 'Siap kak, barang sudah di-packing kayu dan siap pick-up.',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('order_messages', [
            'order_id' => $order->id,
            'user_id' => $admin->id,
            'sender_type' => 'admin',
            'message' => 'Siap kak, barang sudah di-packing kayu dan siap pick-up.',
        ]);
    }

    public function test_multiple_examiners_cannot_view_or_send_messages_to_each_others_orders(): void
    {
        $penguji1 = User::factory()->create(['name' => 'Penguji 1']);
        $penguji2 = User::factory()->create(['name' => 'Penguji 2']);

        $order1 = Order::factory()->create(['user_id' => $penguji1->id]);
        $order2 = Order::factory()->create(['user_id' => $penguji2->id]);

        $order1->messages()->create([
            'user_id' => $penguji1->id,
            'sender_type' => 'customer',
            'message' => 'Pesan rahasia dari Penguji 1',
        ]);

        // Penguji 2 mencoba mengintip chat milik Penguji 1
        Sanctum::actingAs($penguji2);
        $resForbiddenRead = $this->getJson(route('api.v1.orders.messages.index', $order1));
        $resForbiddenRead->assertForbidden();

        // Penguji 2 mencoba mengirim pesan ke order milik Penguji 1
        $resForbiddenSend = $this->postJson(route('api.v1.orders.messages.store', $order1), [
            'message' => 'Spam dari akun lain',
        ]);
        $resForbiddenSend->assertForbidden();

        // Penguji 1 bisa mengakses dan melihat pesan miliknya sendiri
        Sanctum::actingAs($penguji1);
        $resAllowedRead = $this->getJson(route('api.v1.orders.messages.index', $order1));
        $resAllowedRead->assertOk()
            ->assertJsonPath('data.0.message', 'Pesan rahasia dari Penguji 1');
    }

    public function test_admin_can_poll_and_send_messages_via_ajax(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['name' => 'Fajar Customer']);
        $order = Order::factory()->create(['user_id' => $customer->id]);

        $order->messages()->create([
            'user_id' => $customer->id,
            'sender_type' => 'customer',
            'message' => 'Halo admin, butuh bantuan instalasi.',
        ]);

        // Test polling endpoint
        $pollResponse = $this->actingAs($admin)->getJson(route('admin.chats.show', $order));
        $pollResponse->assertOk()
            ->assertJsonStructure(['messages'])
            ->assertJsonCount(1, 'messages')
            ->assertJsonPath('messages.0.sender_type', 'customer')
            ->assertJsonPath('messages.0.message', 'Halo admin, butuh bantuan instalasi.');

        // Test AJAX send endpoint
        $sendResponse = $this->actingAs($admin)->postJson(route('admin.orders.messages.store', $order), [
            'message' => 'Siap kak, tim teknisi kami segera meluncur.',
        ]);

        $sendResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message.sender_type', 'admin')
            ->assertJsonPath('message.message', 'Siap kak, tim teknisi kami segera meluncur.');
    }
}
