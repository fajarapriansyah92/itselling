<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_endpoint_lists_only_active_products(): void
    {
        Product::factory()->create(['name' => 'Router MikroTik']);
        Product::factory()->inactive()->create(['name' => 'Produk Lama']);

        $this->getJson('/api/v1/products')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Router MikroTik')
            ->assertJsonStructure(['data' => [['id', 'name', 'price', 'stock', 'category' => ['slug']]], 'meta' => ['current_page', 'last_page']]);
    }

    public function test_products_can_be_filtered_by_category_and_search(): void
    {
        $security = Category::factory()->create(['slug' => 'software-keamanan']);
        Product::factory()->for($security)->create(['name' => 'Kaspersky Endpoint']);
        Product::factory()->for($security)->create(['name' => 'ESET Protect']);
        Product::factory()->create(['name' => 'Kaspersky Lain Kategori']);

        $this->getJson('/api/v1/products?category=software-keamanan&search=kaspersky')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Kaspersky Endpoint');
    }

    public function test_product_detail_is_found_by_slug(): void
    {
        $product = Product::factory()->service()->create();

        $this->getJson("/api/v1/products/{$product->slug}")
            ->assertOk()
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.stock', null)
            ->assertJsonPath('data.is_available', true);
    }

    public function test_inactive_product_detail_is_not_found(): void
    {
        $product = Product::factory()->inactive()->create();

        $this->getJson("/api/v1/products/{$product->slug}")->assertNotFound();
    }

    public function test_categories_include_active_product_counts(): void
    {
        $category = Category::factory()->create();
        Product::factory()->for($category)->count(2)->create();
        Product::factory()->for($category)->inactive()->create();

        $this->getJson('/api/v1/categories')
            ->assertOk()
            ->assertJsonPath('data.0.products_count', 2);
    }
}
