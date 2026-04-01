<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EmployeeAuthController extends Controller
{
    /**
     * Показать форму входа для сотрудников
     */
    public function showLoginForm(): View
    {
        return view('auth.employee-login');
    }

    /**
     * Обработка входа сотрудника (email/пароль)
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // Если пользователь не сотрудник — перенаправляем на правильную форму
        if (!$user->role->isEmployee()) {
            // Кандидат попал на форму сотрудника — редиректим
            if ($user->role === \App\Enums\UserRole::Candidate) {
                return redirect()->route('vacant.index');
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Доступ только для сотрудников компании.',
            ]);
        }

        return redirect()->intended(route('employee.dashboard'))
            ->with('success', 'Добро пожаловать, ' . $user->name . '!');
    }
}
