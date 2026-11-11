<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    // Buat akun utama Administrator untuk login ke Web Control Panel
    public function run(): void
    {
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
    }
}
