<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_number' => Order::generateOrderNumber(),
            'user_id' => User::factory(),
            'status' => OrderStatus::Pending,
            'total' => fake()->numberBetween(100, 10000) * 1000,
            'recipient_name' => fake()->name(),
            'recipient_phone' => fake()->numerify('08##########'),
            'shipping_address' => fake()->address(),
            'notes' => null,
        ];
    }

    public function status(OrderStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
            'paid_at' => in_array($status, OrderStatus::revenueStatuses(), true) ? now() : null,
            'completed_at' => $status === OrderStatus::Completed ? now() : null,
            'cancelled_at' => $status === OrderStatus::Cancelled ? now() : null,
        ]);
    }
}
