<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_login_page_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_candidate_login_page_can_be_rendered(): void
    {
        $response = $this->get('/vacant/login');
        $response->assertStatus(200);
    }

    public function test_admin_login_page_can_be_rendered(): void
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
    }

    public function test_candidate_can_login(): void
    {
        $user = User::factory()->candidate()->create();

        $response = $this->post('/vacant/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect();
    }

    public function test_employee_can_login(): void
    {
        $user = User::factory()->employee()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect();
    }

    public function test_hr_can_login(): void
    {
        $user = User::factory()->hr()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect();
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_candidate_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->candidate()->create();

        $this->post('/vacant/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post('/logout');

        $this->assertGuest();
        $response->assertRedirect();
    }

    public function test_guest_is_redirected_from_profile(): void
    {
        $response = $this->get('/profile');
        $response->assertRedirect('/login');
    }

    public function test_login_validates_email_required(): void
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_login_validates_password_required(): void
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_login_validates_email_format(): void
    {
        $response = $this->post('/login', [
            'email' => 'not-an-email',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    }
}
