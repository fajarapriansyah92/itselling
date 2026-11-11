<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductReview>
 */
class ProductReviewFactory extends Factory
{
    protected $model = ProductReview::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_id' => Order::factory(),
            'order_item_id' => OrderItem::factory(),
            'product_id' => Product::factory(),
            'rating' => fake()->numberBetween(4, 5),
            'comment' => fake()->sentence(10),
            'admin_reply' => null,
            'admin_replied_at' => null,
        ];
    }
}
