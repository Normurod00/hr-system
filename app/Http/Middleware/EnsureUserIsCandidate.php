<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsCandidate
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->route('candidate.login');
        }

        if (!$request->user()->isCandidate()) {
            // Перенаправляем сотрудников/админов на их страницы
            if ($request->user()->canAccessAdmin()) {
                return redirect()->route('admin.dashboard');
            }

            if ($request->user()->isEmployee()) {
                return redirect()->route('employee.dashboard');
            }

            abort(403, 'Этот раздел доступен только для кандидатов.');
        }

        return $next($request);
    }
}
