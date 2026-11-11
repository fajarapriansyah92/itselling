<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Membuat riwayat transaksi 6 bulan terakhir sebagai data contoh dashboard dan laporan.
     */
    public function run(): void
    {
        $customers = User::factory()->count(8)->create()
            ->push(User::query()->where('email', 'pelanggan@itselling.test')->firstOrFail());
        $products = Product::all();

        foreach (range(1, 45) as $index) {
            $orderedAt = now()->subDays(fake()->numberBetween(0, 180))->setTime(fake()->numberBetween(8, 20), fake()->numberBetween(0, 59));
            $status = $this->statusFor($orderedAt->diffInDays(now()));
            $customer = $customers->random();

            $order = Order::create([
                'order_number' => 'INV-'.$orderedAt->format('Ymd').'-'.str_pad((string) $index, 6, '0', STR_PAD_LEFT),
                'user_id' => $customer->id,
                'status' => $status,
                'total' => 0,
                'recipient_name' => $customer->name,
                'recipient_phone' => $customer->phone,
                'shipping_address' => $customer->address,
                'paid_at' => in_array($status, OrderStatus::revenueStatuses(), true) ? $orderedAt->copy()->addHours(5) : null,
                'completed_at' => $status === OrderStatus::Completed ? $orderedAt->copy()->addDays(3) : null,
                'cancelled_at' => $status === OrderStatus::Cancelled ? $orderedAt->copy()->addDay() : null,
                'created_at' => $orderedAt,
                'updated_at' => $orderedAt,
            ]);

            $total = 0;

            foreach ($products->random(fake()->numberBetween(1, 3)) as $product) {
                $quantity = $product->stock === null ? 1 : fake()->numberBetween(1, 5);
                $subtotal = (float) $product->price * $quantity;
                $total += $subtotal;

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                    'created_at' => $orderedAt,
                    'updated_at' => $orderedAt,
                ]);
            }

            $order->update(['total' => $total]);
        }
    }

    /**
     * Pesanan lama umumnya sudah selesai, pesanan baru masih berjalan.
     */
    private function statusFor(float $ageInDays): OrderStatus
    {
        if ($ageInDays > 14) {
            return fake()->randomElement([
                OrderStatus::Completed, OrderStatus::Completed, OrderStatus::Completed,
                OrderStatus::Completed, OrderStatus::Cancelled,
            ]);
        }

        return fake()->randomElement(OrderStatus::cases());
    }
}
