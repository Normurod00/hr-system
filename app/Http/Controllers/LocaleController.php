<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class LocaleController extends Controller
{
    protected array $supported = ['ru', 'uz', 'en'];

    public function switch(string $locale): RedirectResponse
    {
        if (!in_array($locale, $this->supported)) {
            abort(400, 'Unsupported locale');
        }

        if (auth()->check()) {
            auth()->user()->update(['preferred_locale' => $locale]);
        }

        return redirect()->back()->withCookie(
            cookie('locale', $locale, 60 * 24 * 365) // 1 year
        );
    }
}
