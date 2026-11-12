<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class WebSecurityHeaders
{
    /**
     * Handle an incoming web request and apply core security headers.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // Clickjacking protection: only allow framing from same origin
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // MIME-type sniffing defense
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Cross-origin referrer information control
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // XSS filter legacy instruction
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Permissions Policy
        $response->headers->set('Permissions-Policy', 'camera=(self), microphone=(), geolocation=(self)');

        // Content Security Policy: Strict origin control while explicitly allowing verified CDNs
        $cspDirectives = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' blob: https://cdn.tailwindcss.com https://unpkg.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://cdn.tiny.cloud https://www.googletagmanager.com https://*.googletagmanager.com https://www.google-analytics.com https://ssl.google-analytics.com https://*.google-analytics.com https://*.google.com https://*.google.co.id https://js.pusher.com https://*.pusher.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://unpkg.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
            "worker-src 'self' blob:",
            "font-src 'self' data: https://fonts.gstatic.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
            "img-src 'self' data: blob: https://cdn.cooca.id https://images.unsplash.com https://*.tile.openstreetmap.org https://unpkg.com https://tripay.co.id https://*.tripay.co.id https://www.googletagmanager.com https://*.googletagmanager.com https://www.google-analytics.com https://ssl.google-analytics.com https://*.google-analytics.com https://*.google.com https://*.google.co.id https://*.doubleclick.net https://stats.g.doubleclick.net https://*.fbcdn.net https://*.cdninstagram.com https://*.facebook.com https://*.tiktokcdn.com https://api.qrserver.com https://*.googleusercontent.com https://*.gravatar.com",
            "connect-src 'self' https://api.biteship.com https://tripay.co.id https://*.tripay.co.id https://nominatim.openstreetmap.org https://*.tile.openstreetmap.org https://www.google-analytics.com https://analytics.google.com https://*.google-analytics.com https://www.googletagmanager.com https://*.googletagmanager.com https://stats.g.doubleclick.net https://*.doubleclick.net https://*.google.com https://*.google.co.id https://cdn.jsdelivr.net https://unpkg.com https://cdnjs.cloudflare.com https://*.pusher.com https://*.pusherapp.com wss: ws:",
            "frame-src 'self' https://tripay.co.id https://*.tripay.co.id",
            "object-src 'none'",
            "base-uri 'self'",
        ];
        $response->headers->set('Content-Security-Policy', implode('; ', $cspDirectives));

        return $response;
    }
}
