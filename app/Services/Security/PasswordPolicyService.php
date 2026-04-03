<?php

namespace App\Services\Security;

use App\Models\Security\PasswordHistory;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PasswordPolicyService
{
    const MIN_LENGTH = 8;
    const REQUIRE_UPPERCASE = true;
    const REQUIRE_LOWERCASE = true;
    const REQUIRE_NUMBER = true;
    const REQUIRE_SPECIAL = true;
    const MAX_AGE_DAYS_ADMIN = 90;
    const MAX_AGE_DAYS_EMPLOYEE = 180;
    const HISTORY_COUNT = 5;

    /**
     * Валидация пароля по политике
     * @return string[] массив ошибок (пустой = ок)
     */
    public function validate(string $password): array
    {
        $errors = [];

        if (mb_strlen($password) < self::MIN_LENGTH) {
            $errors[] = 'Пароль должен содержать минимум ' . self::MIN_LENGTH . ' символов';
        }

        if (self::REQUIRE_UPPERCASE && !preg_match('/[A-ZА-ЯЁ]/u', $password)) {
            $errors[] = 'Пароль должен содержать заглавную букву';
        }

        if (self::REQUIRE_LOWERCASE && !preg_match('/[a-zа-яё]/u', $password)) {
            $errors[] = 'Пароль должен содержать строчную букву';
        }

        if (self::REQUIRE_NUMBER && !preg_match('/[0-9]/', $password)) {
            $errors[] = 'Пароль должен содержать цифру';
        }

        if (self::REQUIRE_SPECIAL && !preg_match('/[!@#$%^&*()_+\-=\[\]{}|;:,.<>?\/\\\\~`]/', $password)) {
            $errors[] = 'Пароль должен содержать спецсимвол (!@#$%^&*...)';
        }

        return $errors;
    }

    /**
     * Использовался ли пароль ранее
     */
    public function isReused(User $user, string $plainPassword): bool
    {
        return PasswordHistory::wasUsedBefore($user->id, $plainPassword, self::HISTORY_COUNT);
    }

    /**
     * Истёк ли пароль
     */
    public function isExpired(User $user): bool
    {
        $lastChange = PasswordHistory::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->value('created_at');

        if (!$lastChange) {
            return false; // Нет истории — не считаем истёкшим
        }

        $maxDays = $user->canAccessAdmin()
            ? self::MAX_AGE_DAYS_ADMIN
            : self::MAX_AGE_DAYS_EMPLOYEE;

        return $lastChange->diffInDays(now()) > $maxDays;
    }

    /**
     * Сохранить пароль в историю при смене
     */
    public function recordPasswordChange(User $user): void
    {
        PasswordHistory::savePassword($user->id, $user->password);
    }
}
