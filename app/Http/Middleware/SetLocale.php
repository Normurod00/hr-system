<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    protected array $supported = ['ru', 'uz', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->cookie('locale')
            ?? $request->user()?->preferred_locale
            ?? config('app.locale', 'ru');

        if (in_array($locale, $this->supported)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
