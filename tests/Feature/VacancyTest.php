<?php

namespace Tests\Feature;

use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VacancyTest extends TestCase
{
    use RefreshDatabase;

    public function test_vacancy_listing_is_accessible(): void
    {
        $response = $this->get('/vacant');
        $response->assertStatus(200);
    }

    public function test_vacancy_listing_is_accessible_as_guest(): void
    {
        $this->assertGuest();

        $response = $this->get('/vacant');
        $response->assertStatus(200);
    }

    public function test_vacancy_detail_page_works(): void
    {
        $vacancy = Vacancy::factory()->create(['is_active' => true]);

        $response = $this->get('/vacant/' . $vacancy->id);
        $response->assertStatus(200);
    }

    public function test_vacancy_detail_returns_404_for_inactive_vacancy(): void
    {
        $vacancy = Vacancy::factory()->inactive()->create();

        $response = $this->get('/vacant/' . $vacancy->id);
        // Controller aborts with 404 for inactive vacancies
        $response->assertStatus(404);
    }

    public function test_vacancy_detail_returns_404_for_nonexistent(): void
    {
        $response = $this->get('/vacant/99999');
        $response->assertStatus(404);
    }

    public function test_old_vacancies_url_redirects(): void
    {
        $response = $this->get('/vacancies');
        $response->assertRedirect(route('vacant.index'));
    }
}
