<?php

namespace Tests\Unit\Enums;

use App\Enums\UserRole;
use PHPUnit\Framework\TestCase;

class UserRoleTest extends TestCase
{
    public function test_all_roles_exist(): void
    {
        $cases = UserRole::cases();
        $this->assertNotEmpty($cases);
        $this->assertCount(4, $cases);
    }

    public function test_expected_roles_are_defined(): void
    {
        $values = array_map(fn ($c) => $c->value, UserRole::cases());

        $this->assertContains('candidate', $values);
        $this->assertContains('employee', $values);
        $this->assertContains('hr', $values);
        $this->assertContains('admin', $values);
    }

    public function test_admin_has_admin_access(): void
    {
        $this->assertTrue(UserRole::Admin->hasAdminAccess());
    }

    public function test_hr_has_admin_access(): void
    {
        $this->assertTrue(UserRole::Hr->hasAdminAccess());
    }

    public function test_candidate_has_no_admin_access(): void
    {
        $this->assertFalse(UserRole::Candidate->hasAdminAccess());
    }

    public function test_employee_has_no_admin_access(): void
    {
        $this->assertFalse(UserRole::Employee->hasAdminAccess());
    }

    public function test_employee_roles_are_employees(): void
    {
        $this->assertTrue(UserRole::Employee->isEmployee());
        $this->assertTrue(UserRole::Hr->isEmployee());
        $this->assertTrue(UserRole::Admin->isEmployee());
    }

    public function test_candidate_is_not_employee(): void
    {
        $this->assertFalse(UserRole::Candidate->isEmployee());
    }

    public function test_each_role_has_label(): void
    {
        foreach (UserRole::cases() as $role) {
            $this->assertNotEmpty($role->label(), "Role {$role->value} should have a label");
        }
    }

    public function test_each_role_has_color(): void
    {
        foreach (UserRole::cases() as $role) {
            $this->assertNotEmpty($role->color(), "Role {$role->value} should have a color");
        }
    }

    public function test_specific_labels(): void
    {
        $this->assertEquals('Кандидат', UserRole::Candidate->label());
        $this->assertEquals('Сотрудник', UserRole::Employee->label());
        $this->assertEquals('HR-менеджер', UserRole::Hr->label());
        $this->assertEquals('Администратор', UserRole::Admin->label());
    }

    public function test_values_returns_all_string_values(): void
    {
        $values = UserRole::values();
        $this->assertCount(4, $values);
        $this->assertContains('candidate', $values);
        $this->assertContains('admin', $values);
    }
}
