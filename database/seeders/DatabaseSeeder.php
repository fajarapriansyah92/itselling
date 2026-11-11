<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1 Akun Admin Tunggal untuk Web Control Panel
        User::updateOrCreate(
            ['email' => 'admin@itselling.test'],
            [
                'name' => 'Administrator',
                'password' => 'password',
                'role' => UserRole::Admin,
                'phone' => '081234567890',
                'address' => 'Jakarta, Indonesia',
            ]
        );

        // 1 Akun Pelanggan Demo untuk Login Aplikasi Mobile (Flutter)
        User::updateOrCreate(
            ['email' => 'pelanggan@itselling.test'],
            [
                'name' => 'Pelanggan Demo',
                'password' => 'password',
                'role' => UserRole::Customer,
                'phone' => '089876543210',
                'address' => 'Jl. Pengadaan No. 12, Jakarta Barat',
            ]
        );

        $this->call([
            CatalogSeeder::class,
        ]);
    }
}
