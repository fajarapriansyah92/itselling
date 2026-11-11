<?php

namespace Database\Factories;

use App\Enums\ProductType;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::title(fake()->unique()->words(3, true));

        return [
            'category_id' => Category::factory(),
            'sku' => Str::upper(fake()->unique()->bothify('PRD-####??')),
            'name' => $name,
            'slug' => Str::slug($name),
            'type' => ProductType::Hardware,
            'brand' => fake()->company(),
            'unit' => 'unit',
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'price' => fake()->numberBetween(100, 10000) * 1000,
            'stock' => fake()->numberBetween(5, 50),
            'is_active' => true,
        ];
    }

    public function service(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ProductType::Service,
            'unit' => 'paket',
            'stock' => null,
        ]);
    }

    public function software(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ProductType::Software,
            'unit' => 'lisensi',
            'stock' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
