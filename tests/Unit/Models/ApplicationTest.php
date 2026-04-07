<?php

namespace Tests\Unit\Models;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{
    public function test_match_score_color_returns_green_for_high_score(): void
    {
        $app = new Application();
        $app->match_score = 85;

        $this->assertEquals('green', $app->match_score_color);
    }

    public function test_match_score_color_returns_green_at_boundary(): void
    {
        $app = new Application();
        $app->match_score = 80;

        $this->assertEquals('green', $app->match_score_color);
    }

    public function test_match_score_color_returns_yellow_for_medium_score(): void
    {
        $app = new Application();
        $app->match_score = 65;

        $this->assertEquals('yellow', $app->match_score_color);
    }

    public function test_match_score_color_returns_yellow_at_boundary(): void
    {
        $app = new Application();
        $app->match_score = 60;

        $this->assertEquals('yellow', $app->match_score_color);
    }

    public function test_match_score_color_returns_orange_for_mid_low_score(): void
    {
        $app = new Application();
        $app->match_score = 50;

        $this->assertEquals('orange', $app->match_score_color);
    }

    public function test_match_score_color_returns_orange_at_boundary(): void
    {
        $app = new Application();
        $app->match_score = 40;

        $this->assertEquals('orange', $app->match_score_color);
    }

    public function test_match_score_color_returns_red_for_low_score(): void
    {
        $app = new Application();
        $app->match_score = 20;

        $this->assertEquals('red', $app->match_score_color);
    }

    public function test_match_score_color_returns_red_for_zero(): void
    {
        $app = new Application();
        $app->match_score = 0;

        $this->assertEquals('red', $app->match_score_color);
    }

    public function test_match_score_color_returns_gray_for_null(): void
    {
        $app = new Application();
        $app->match_score = null;

        $this->assertEquals('gray', $app->match_score_color);
    }

    public function test_match_score_bg_class_matches_color(): void
    {
        $app = new Application();

        $app->match_score = 90;
        $this->assertStringContainsString('green', $app->match_score_bg_class);

        $app->match_score = 70;
        $this->assertStringContainsString('yellow', $app->match_score_bg_class);

        $app->match_score = 50;
        $this->assertStringContainsString('orange', $app->match_score_bg_class);

        $app->match_score = 20;
        $this->assertStringContainsString('red', $app->match_score_bg_class);

        $app->match_score = null;
        $this->assertStringContainsString('gray', $app->match_score_bg_class);
    }

    public function test_fillable_contains_required_fields(): void
    {
        $app = new Application();
        $fillable = $app->getFillable();

        $this->assertContains('user_id', $fillable);
        $this->assertContains('vacancy_id', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('match_score', $fillable);
        $this->assertContains('cover_letter', $fillable);
        $this->assertContains('source', $fillable);
        $this->assertContains('notes', $fillable);
    }

    public function test_status_label_for_null_status(): void
    {
        $app = new Application();
        // status is null by default (not set)
        $this->assertEquals('Неизвестно', $app->status_label);
    }

    public function test_status_color_for_null_status(): void
    {
        $app = new Application();
        $this->assertEquals('gray', $app->status_color);
    }

    public function test_status_bg_class_for_null_status(): void
    {
        $app = new Application();
        $this->assertEquals('bg-gray-100 text-gray-700', $app->status_bg_class);
    }

    public function test_casts_include_status_enum(): void
    {
        $app = new Application();
        $casts = $app->getCasts();

        $this->assertArrayHasKey('status', $casts);
        $this->assertEquals(ApplicationStatus::class, $casts['status']);
    }

    public function test_casts_include_match_score_integer(): void
    {
        $app = new Application();
        $casts = $app->getCasts();

        $this->assertArrayHasKey('match_score', $casts);
        $this->assertEquals('integer', $casts['match_score']);
    }
}
