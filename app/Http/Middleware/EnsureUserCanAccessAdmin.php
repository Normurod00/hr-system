<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanAccessAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->route('admin.login');
        }

        if (!$request->user()->canAccessAdmin()) {
            // Перенаправляем на правильную страницу по роли
            if ($request->user()->isCandidate()) {
                return redirect()->route('vacant.index');
            }

            if ($request->user()->isEmployee()) {
                return redirect()->route('employee.dashboard');
            }

            abort(403, 'У вас нет доступа к панели управления.');
        }

        return $next($request);
    }
}
