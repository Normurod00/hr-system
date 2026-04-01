<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CandidateAuthController extends Controller
{
    /**
     * Показать форму входа для кандидатов
     */
    public function showLoginForm(): View
    {
        return view('auth.candidate-login');
    }

    /**
     * Обработка входа кандидата (email/пароль)
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // Если пользователь не кандидат — перенаправляем на правильную форму
        if ($user->role !== \App\Enums\UserRole::Candidate) {
            // Не разлогиниваем — просто редиректим на нужную страницу
            if ($user->role->hasAdminAccess()) {
                return redirect()->route('admin.dashboard');
            }

            if ($user->role->isEmployee()) {
                return redirect()->route('employee.dashboard');
            }

            // Fallback — разлогинить и показать ошибку
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('info', 'Вы вошли как сотрудник. Используйте форму для сотрудников.');
        }

        return redirect()->intended(route('vacant.index'))
            ->with('success', 'Добро пожаловать, ' . $user->name . '!');
    }
}
