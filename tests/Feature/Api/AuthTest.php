<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_login_returns_token(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'token',
                'token_type',
                'user' => ['id', 'name', 'email'],
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals('Bearer', $response->json('token_type'));
    }

    public function test_api_login_returns_user_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('user.name', 'Test User')
            ->assertJsonPath('user.email', 'test@example.com');
    }

    public function test_api_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'wrong',
        ]);

        $response->assertStatus(401)
            ->assertJson(['success' => false]);
    }

    public function test_api_login_validates_email_required(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'password' => 'password',
        ]);

        $response->assertStatus(422);
    }

    public function test_api_login_validates_password_required(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(422);
    }

    public function test_api_login_validates_email_format(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'not-an-email',
            'password' => 'password',
        ]);

        $response->assertStatus(422);
    }

    public function test_api_login_stores_token_in_database(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $user->refresh();
        $this->assertNotNull($user->api_token);
        $this->assertNotNull($user->api_token_expires_at);
        $this->assertTrue($user->api_token_expires_at->isFuture());
    }
}
