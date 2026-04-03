<?php

namespace App\Http\Requests\Auth;

use App\Models\Security\LoginAttempt;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Введите email',
            'email.email' => 'Введите корректный email',
            'password.required' => 'Введите пароль',
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // Проверка lockout по login_attempts
        if (LoginAttempt::isLocked($this->input('email'))) {
            LoginAttempt::record(
                $this->input('email'), $this->ip(), false, 'account_locked', null, $this->userAgent()
            );

            throw ValidationException::withMessages([
                'email' => 'Аккаунт временно заблокирован из-за множества неудачных попыток. Попробуйте через 15 минут.',
            ]);
        }

        if (!Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            LoginAttempt::record(
                $this->input('email'), $this->ip(), false, 'wrong_password', null, $this->userAgent()
            );

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        LoginAttempt::record(
            $this->input('email'), $this->ip(), true, null, Auth::id(), $this->userAgent()
        );
    }

    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')) . '|' . $this->ip());
    }
}
