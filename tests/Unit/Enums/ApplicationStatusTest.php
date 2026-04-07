<?php

namespace Tests\Unit\Enums;

use App\Enums\ApplicationStatus;
use PHPUnit\Framework\TestCase;

class ApplicationStatusTest extends TestCase
{
    public function test_all_statuses_exist(): void
    {
        $cases = ApplicationStatus::cases();
        $this->assertNotEmpty($cases);
        $this->assertCount(5, $cases);
    }

    public function test_expected_statuses_are_defined(): void
    {
        $values = array_map(fn ($c) => $c->value, ApplicationStatus::cases());

        $this->assertContains('new', $values);
        $this->assertContains('in_review', $values);
        $this->assertContains('invited', $values);
        $this->assertContains('rejected', $values);
        $this->assertContains('hired', $values);
    }

    public function test_each_status_has_label(): void
    {
        foreach (ApplicationStatus::cases() as $status) {
            $this->assertNotEmpty($status->label(), "Status {$status->value} should have a label");
        }
    }

    public function test_each_status_has_color(): void
    {
        foreach (ApplicationStatus::cases() as $status) {
            $this->assertNotEmpty($status->color(), "Status {$status->value} should have a color");
        }
    }

    public function test_each_status_has_bg_class(): void
    {
        foreach (ApplicationStatus::cases() as $status) {
            $bgClass = $status->bgClass();
            $this->assertNotEmpty($bgClass, "Status {$status->value} should have a bgClass");
            $this->assertStringContainsString('bg-', $bgClass);
            $this->assertStringContainsString('text-', $bgClass);
        }
    }

    public function test_status_can_be_created_from_value(): void
    {
        foreach (ApplicationStatus::cases() as $status) {
            $fromValue = ApplicationStatus::from($status->value);
            $this->assertEquals($status, $fromValue);
        }
    }

    public function test_try_from_returns_null_for_invalid(): void
    {
        $this->assertNull(ApplicationStatus::tryFrom('invalid_status'));
    }

    public function test_values_returns_all_string_values(): void
    {
        $values = ApplicationStatus::values();
        $this->assertCount(5, $values);
        $this->assertContains('new', $values);
        $this->assertContains('hired', $values);
    }

    public function test_specific_labels(): void
    {
        $this->assertEquals('Новая', ApplicationStatus::New->label());
        $this->assertEquals('На рассмотрении', ApplicationStatus::InReview->label());
        $this->assertEquals('Приглашён', ApplicationStatus::Invited->label());
        $this->assertEquals('Отклонён', ApplicationStatus::Rejected->label());
        $this->assertEquals('Принят', ApplicationStatus::Hired->label());
    }

    public function test_specific_colors(): void
    {
        $this->assertEquals('blue', ApplicationStatus::New->color());
        $this->assertEquals('yellow', ApplicationStatus::InReview->color());
        $this->assertEquals('purple', ApplicationStatus::Invited->color());
        $this->assertEquals('red', ApplicationStatus::Rejected->color());
        $this->assertEquals('green', ApplicationStatus::Hired->color());
    }
}
