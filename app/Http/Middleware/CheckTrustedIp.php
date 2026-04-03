<?php

namespace App\Http\Middleware;

use App\Models\Security\TrustedIp;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Проверяет IP пользователя по whitelist для admin-доступа.
 * Если whitelist пуст — пропускает всех (не настроено).
 */
class CheckTrustedIp
{
    public function handle(Request $request, Closure $next, string $scope = 'admin'): Response
    {
        $ip = $request->ip();

        if (!TrustedIp::isAllowed($ip, $scope)) {
            abort(403, 'Доступ запрещён: ваш IP-адрес не в списке доверенных');
        }

        return $next($request);
    }
}
