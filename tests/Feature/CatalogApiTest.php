<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_returns_active_products_with_their_category(): void
    {
        $category = Category::create(['name' => 'Jasa IT', 'slug' => 'jasa-it']);
        Product::create([
            'category_id' => $category->id, 'name' => 'Konfigurasi Jaringan',
            'slug' => 'konfigurasi-jaringan', 'type' => 'service',
            'short_description' => 'Konfigurasi jaringan kantor.',
            'description' => 'Konfigurasi router dan Wi-Fi untuk kantor.', 'price' => 2500000,
        ]);

        $this->getJson('/api/v1/products')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Konfigurasi Jaringan')
            ->assertJsonPath('data.0.category.slug', 'jasa-it');
    }

    public function test_authenticated_mobile_user_can_send_product_message(): void
    {
        $category = Category::create(['name' => 'Keamanan Siber', 'slug' => 'keamanan-siber']);
        $product = Product::create([
            'category_id' => $category->id, 'name' => 'Endpoint Security',
            'slug' => 'endpoint-security', 'type' => 'software',
            'short_description' => 'Lisensi keamanan endpoint.',
            'description' => 'Proteksi endpoint untuk bisnis.', 'price' => 350000,
        ]);
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')->postJson("/api/v1/products/{$product->slug}/messages", [
            'message' => 'Apakah lisensi ini ready stock?',
        ])->assertCreated()->assertJsonPath('data.message', 'Apakah lisensi ini ready stock?');
    }
}
