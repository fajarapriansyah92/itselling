<?php

namespace Tests\Feature\Admin;

use App\Enums\OrderStatus;
use App\Models\AdminNotification;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_customer_account_cannot_log_in_to_admin_panel(): void
    {
        $customer = User::factory()->create();

        $this->post('/login', ['email' => $customer->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_can_log_in(): void
    {
        $admin = User::factory()->admin()->create();

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_pages_render(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->status(OrderStatus::Completed)->create();
        OrderItem::factory()->for($order)->create();

        foreach ([
            route('admin.dashboard'),
            route('admin.orders.index'),
            route('admin.orders.show', $order),
            route('admin.products.index'),
            route('admin.products.create'),
            route('admin.categories.index'),
            route('admin.customers.index'),
            route('admin.customers.show', $order->user),
            route('admin.reports.sales'),
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_admin_can_create_a_product(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'sku' => 'ITS-900',
            'name' => 'Instalasi Firewall',
            'type' => 'service',
            'unit' => 'paket',
            'short_description' => 'Setup firewall kantor.',
            'description' => 'Instalasi dan konfigurasi firewall.',
            'price' => 3000000,
            'stock' => 99,
            'is_active' => '1',
        ])->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', ['slug' => 'instalasi-firewall', 'stock' => null]);
    }

    public function test_admin_can_upload_and_update_product_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $file = UploadedFile::fake()->create('router.png', 200, 'image/png');

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'sku' => 'HW-ROUTER-01',
            'name' => 'Mikrotik RB750Gr3',
            'type' => 'hardware',
            'unit' => 'unit',
            'short_description' => 'Router 5 port gigabit.',
            'description' => 'Deskripsi router.',
            'price' => 1150000,
            'stock' => 10,
            'is_active' => '1',
            'image' => $file,
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::where('sku', 'HW-ROUTER-01')->firstOrFail();
        $this->assertNotNull($product->image_path);
        Storage::disk('public')->assertExists($product->image_path);
        $this->assertStringContainsString('storage/products', $product->image_url);

        // Test removing image
        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'category_id' => $category->id,
            'sku' => 'HW-ROUTER-01',
            'name' => 'Mikrotik RB750Gr3',
            'type' => 'hardware',
            'unit' => 'unit',
            'short_description' => 'Router 5 port gigabit.',
            'description' => 'Deskripsi router.',
            'price' => 1150000,
            'stock' => 10,
            'is_active' => '1',
            'remove_image' => '1',
        ])->assertRedirect(route('admin.products.index'));

        $this->assertNull($product->fresh()->image_path);
        $this->assertNull($product->fresh()->image_url);

        // Test direct upload button staying on edit page
        $newFile = UploadedFile::fake()->create('new_router.jpg', 200, 'image/jpeg');
        $this->actingAs($admin)->post(route('admin.products.upload-image', $product), [
            'image' => $newFile,
        ])->assertRedirect(route('admin.products.edit', $product));

        $this->assertNotNull($product->fresh()->image_path);

        // Test direct destroy button staying on edit page
        $this->actingAs($admin)->delete(route('admin.products.destroy-image', $product))
            ->assertRedirect(route('admin.products.edit', $product));

        $this->assertNull($product->fresh()->image_path);

        // Test update with stay=1
        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'category_id' => $category->id,
            'sku' => 'HW-ROUTER-01',
            'name' => 'Mikrotik RB750Gr3 Updated',
            'type' => 'hardware',
            'unit' => 'unit',
            'short_description' => 'Router 5 port gigabit.',
            'description' => 'Deskripsi router.',
            'price' => 1200000,
            'stock' => 12,
            'is_active' => '1',
            'stay' => '1',
        ])->assertRedirect(route('admin.products.edit', $product));
    }

    public function test_admin_can_move_order_through_allowed_statuses_only(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->create();

        $this->actingAs($admin)->patch(route('admin.orders.update', $order), ['status' => 'completed'])
            ->assertSessionHasErrors('status');

        $this->actingAs($admin)->patch(route('admin.orders.update', $order), ['status' => 'paid'])
            ->assertSessionHasNoErrors();

        $this->assertSame(OrderStatus::Paid, $order->fresh()->status);
        $this->assertNotNull($order->fresh()->paid_at);
    }

    public function test_admin_can_process_hardware_order_with_shipping_and_tracking_number(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->create(['status' => OrderStatus::Paid]);

        // Paid -> Processing (Warehouse packing)
        $this->actingAs($admin)
            ->patch(route('admin.orders.update', $order), ['status' => 'processing'])
            ->assertSessionHasNoErrors();
        $this->assertSame(OrderStatus::Processing, $order->fresh()->status);

        // Processing -> Shipped with tracking number
        $this->actingAs($admin)
            ->patch(route('admin.orders.update', $order), [
                'status' => 'shipped',
                'tracking_number' => 'JNE-UPS-123456789',
            ])
            ->assertSessionHasNoErrors();

        $refreshed = $order->fresh();
        $this->assertSame(OrderStatus::Shipped, $refreshed->status);
        $this->assertSame('JNE-UPS-123456789', $refreshed->tracking_number);

        // Shipped -> Completed
        $this->actingAs($admin)
            ->patch(route('admin.orders.update', $order), ['status' => 'completed'])
            ->assertSessionHasNoErrors();

        $completed = $order->fresh();
        $this->assertSame(OrderStatus::Completed, $completed->status);
        $this->assertNotNull($completed->completed_at);
    }

    public function test_sales_report_only_counts_paid_orders_in_range(): void
    {
        $admin = User::factory()->admin()->create();
        Order::factory()->status(OrderStatus::Completed)->create(['total' => 1500000]);
        Order::factory()->status(OrderStatus::Pending)->create(['total' => 9900000]);
        Order::factory()->status(OrderStatus::Completed)->create(['total' => 7700000, 'created_at' => now()->subYear()]);

        $this->actingAs($admin)
            ->get(route('admin.reports.sales'))
            ->assertOk()
            ->assertViewHas('revenueTotal', fn ($total): bool => (float) $total === 1500000.0);
    }

    public function test_product_with_orders_is_deactivated_instead_of_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create();
        OrderItem::factory()->for($product)->create();

        $this->actingAs($admin)->delete(route('admin.products.destroy', $product));

        $this->assertFalse($product->fresh()->is_active);
    }

    public function test_admin_can_generate_sku_by_category_type(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->getJson(route('admin.products.generate-sku', ['type' => 'hardware']))
            ->assertOk()
            ->assertJsonPath('sku', 'HDW-001');

        $this->actingAs($admin)
            ->getJson(route('admin.products.generate-sku', ['type' => 'software']))
            ->assertOk()
            ->assertJsonPath('sku', 'LIC-001');

        $this->actingAs($admin)
            ->getJson(route('admin.products.generate-sku', ['type' => 'service']))
            ->assertOk()
            ->assertJsonPath('sku', 'SRV-001');
    }

    public function test_admin_can_create_product_with_automatic_sku(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        // Submit form without specifying SKU and without short_description
        $this->actingAs($admin)->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name' => 'Antivirus Enterprise',
            'type' => 'software',
            'unit' => 'lisensi',
            'description' => 'Lisensi proteksi antivirus terpercaya untuk kantor dan server bisnis.',
            'price' => 500000,
            'stock' => 50,
            'is_active' => '1',
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::where('slug', 'antivirus-enterprise')->firstOrFail();
        $this->assertSame('LIC-001', $product->sku);
        $this->assertSame('Lisensi proteksi antivirus terpercaya untuk kantor dan server bisnis.', $product->short_description);
    }

    public function test_admin_can_view_notifications_index(): void
    {
        $admin = User::factory()->admin()->create();

        AdminNotification::create([
            'type' => 'new_order',
            'title' => 'Pesanan Baru #ORD-001',
            'message' => 'Ada pesanan baru menunggu proses',
            'link' => route('admin.orders.index'),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertSee('Pusat Notifikasi')
            ->assertSee('Pesanan Baru #ORD-001');
    }

    public function test_admin_can_mark_all_notifications_as_read_via_json(): void
    {
        $admin = User::factory()->admin()->create();

        AdminNotification::create([
            'type' => 'new_order',
            'title' => 'Pesanan Baru #ORD-001',
            'message' => 'Ada pesanan baru',
            'read_at' => null,
        ]);
        AdminNotification::create([
            'type' => 'payment',
            'title' => 'Pembayaran Masuk',
            'message' => 'Pembayaran berhasil',
            'read_at' => null,
        ]);

        $this->assertSame(2, AdminNotification::whereNull('read_at')->count());

        $this->actingAs($admin)
            ->postJson(route('admin.notifications.read-all'))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame(0, AdminNotification::whereNull('read_at')->count());
    }

    public function test_admin_can_mark_all_notifications_as_read_via_get_or_post(): void
    {
        $admin = User::factory()->admin()->create();

        AdminNotification::create([
            'type' => 'order_chat',
            'title' => 'Pesan Obrolan Baru',
            'message' => 'Halo admin',
            'read_at' => null,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.notifications.read-all'))
            ->assertRedirect();

        $this->assertSame(0, AdminNotification::whereNull('read_at')->count());
    }

    public function test_admin_can_mark_single_notification_as_read_and_redirect(): void
    {
        $admin = User::factory()->admin()->create();

        $notif = AdminNotification::create([
            'type' => 'new_order',
            'title' => 'Pesanan Baru',
            'message' => 'Ada pesanan',
            'link' => route('admin.dashboard'),
            'read_at' => null,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.notifications.read', $notif))
            ->assertRedirect(route('admin.dashboard'));

        $this->assertNotNull($notif->fresh()->read_at);
    }

    public function test_admin_can_create_four_categories_at_once(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'categories' => [
                    ['name' => 'Laptop & PC Bisnis', 'description' => 'Perangkat kerja'],
                    ['name' => 'Infrastruktur Jaringan', 'description' => 'Router dan switch'],
                    ['name' => 'Lisensi Software', 'description' => 'Lisensi resmi'],
                    ['name' => 'Jasa Teknisi IT', 'description' => 'Instalasi dan servis'],
                ],
            ])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHasNoErrors();

        $this->assertSame(4, Category::count());
        $this->assertDatabaseHas('categories', ['slug' => 'laptop-pc-bisnis']);
        $this->assertDatabaseHas('categories', ['slug' => 'jasa-teknisi-it']);
    }
}
