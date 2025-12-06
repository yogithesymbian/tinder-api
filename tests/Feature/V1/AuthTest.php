<?php

namespace Tests\Feature\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_and_receive_token()
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Alice',
            'email' => 'alice@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['user' => ['id', 'email'], 'token']);
        $this->assertDatabaseHas('users', ['email' => 'alice@example.test']);
    }

    public function test_valid_user_can_login_and_receive_token()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('secretpassword'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'secretpassword',
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'token',
            'user' => [
                'id',
                'name',
                'email',
            ],
        ]);

        $this->assertIsString($response->json('token'));
    }

    public function test_user_can_logout()
    {
        $user = User::factory()->create();

        $token = $user->createToken('test-token')->plainTextToken;
        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logged out']);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    public function test_guest_cannot_logout()
    {
        $response = $this->postJson('/api/v1/logout');
        $response->assertStatus(401);
    }

    public function test_invalid_credentials_prevent_login()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('correctpassword'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422);
        $response->assertJsonMissing(['token']);
    }
}
