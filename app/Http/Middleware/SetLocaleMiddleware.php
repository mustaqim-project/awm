<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    /**
     * Supported locales in COOCA.
     */
    public const SUPPORTED_LOCALES = ['id', 'en'];

    /**
     * Handle an incoming request and bind active locale deterministically.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Prioritize direct query parameter (?lang=en or ?locale=en) for crawlers, links, or instant switch
        $queryLocale = $request->query('lang') ?? $request->query('locale');
        if ($queryLocale && in_array($queryLocale, self::SUPPORTED_LOCALES, true)) {
            $locale = (string) $queryLocale;
            if ($request->hasSession()) {
                $request->session()->put('locale', $locale);
            }
            cookie()->queue(cookie('cooca_locale', $locale, 60 * 24 * 365));
        } else {
            // 2. Fallback to active session
            $locale = $request->hasSession() ? $request->session()->get('locale') : null;

            // 3. Fallback to persistent cookie or default app locale
            if (! $locale) {
                $locale = $request->cookie('cooca_locale', config('app.locale', 'id'));
            }
        }

        if (! in_array($locale, self::SUPPORTED_LOCALES, true)) {
            $locale = 'id';
        }

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
