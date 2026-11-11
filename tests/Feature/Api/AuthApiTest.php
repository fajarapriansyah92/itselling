<?php

namespace Tests\Feature\Api;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_and_receives_a_token(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '081200000000',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'device_name' => 'android',
        ])
            ->assertCreated()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);

        $this->assertSame(UserRole::Customer, User::firstWhere('email', 'budi@example.com')->role);
    }

    public function test_login_with_wrong_password_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'salah-password',
            'device_name' => 'android',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_token_from_login_grants_access_to_profile(): void
    {
        $user = User::factory()->create();

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'android',
        ])->assertOk()->json('token');

        $this->withToken($token)
            ->getJson('/api/v1/profile')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_profile_requires_authentication(): void
    {
        $this->getJson('/api/v1/profile')->assertUnauthorized();
    }
}
