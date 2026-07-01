<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    /** GET /language/{locale} — persist the chosen locale in session and redirect back. */
    public function switch(Request $request, string $locale)
    {
        if (!array_key_exists($locale, SetLocale::SUPPORTED_LOCALES)) {
            abort(404);
        }

        session(['app_locale' => $locale]);

        return back();
    }
}
