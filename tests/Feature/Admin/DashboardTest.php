<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_dashboard(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(200);
    }

    public function test_hr_can_access_dashboard(): void
    {
        $user = User::factory()->hr()->create();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(200);
    }

    public function test_candidate_cannot_access_dashboard(): void
    {
        $user = User::factory()->candidate()->create();

        $response = $this->actingAs($user)->get('/admin');

        // Admin middleware redirects candidates to /vacant
        $response->assertRedirect(route('vacant.index'));
    }

    public function test_employee_cannot_access_dashboard(): void
    {
        $user = User::factory()->employee()->create();

        $response = $this->actingAs($user)->get('/admin');

        // Admin middleware redirects employees to employee dashboard
        $response->assertRedirect(route('employee.dashboard'));
    }

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect(route('admin.login'));
    }
}
