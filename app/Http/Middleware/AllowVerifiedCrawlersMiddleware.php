<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

final class AllowVerifiedCrawlersMiddleware
{
    /**
     * Allowed social preview user-agents (substrings matched case-insensitively).
     *
     * @var array<int, string>
     */
    private const SOCIAL_PREVIEW_BOTS = [
        'facebookexternalhit',
        'whatsapp',
        'linkedinbot',
        'twitterbot',
        'telegrambot',
        'slackbot',
        'discordbot',
        'pinterest',
        'applebot',
    ];

    /**
     * Search engine bots subject to reverse DNS verification.
     *
     * @var array<string, array<int, string>>
     */
    private const SEARCH_ENGINE_DOMAINS = [
        'googlebot' => ['.googlebot.com', '.google.com'],
        'bingbot'   => ['.search.msn.com'],
        'duckduck'  => ['.duckduckgo.com'],
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userAgent = (string) $request->userAgent();
        $ip = (string) $request->ip();

        $isVerifiedCrawler = $this->isVerifiedCrawler($userAgent, $ip);

        if ($isVerifiedCrawler) {
            $request->attributes->set('is_verified_crawler', true);
        }

        /** @var Response $response */
        $response = $next($request);

        if ($isVerifiedCrawler && $this->isPublicMarketingRoute($request)) {
            $response->headers->set('X-Robots-Tag', 'index, follow, max-image-preview:large');
            $response->headers->set('X-Crawler-Verified', '1');
        }

        return $response;
    }

    /**
     * Verify whether the incoming request is from a legitimate bot.
     */
    public function isVerifiedCrawler(string $userAgent, string $ip): bool
    {
        $uaLower = strtolower($userAgent);

        if ($uaLower === '') {
            return false;
        }

        // 1. Social preview bots (instant match)
        foreach (self::SOCIAL_PREVIEW_BOTS as $bot) {
            if (str_contains($uaLower, $bot)) {
                return true;
            }
        }

        // 2. Search engine bot detection with reverse DNS validation
        foreach (self::SEARCH_ENGINE_DOMAINS as $engineKey => $allowedDomains) {
            if (str_contains($uaLower, $engineKey)) {
                return $this->verifyReverseDns($ip, $allowedDomains);
            }
        }

        return false;
    }

    /**
     * Perform forward-confirmed reverse DNS (FCrDNS) check cached for 24 hours.
     *
     * @param  array<int, string>  $allowedDomains
     */
    private function verifyReverseDns(string $ip, array $allowedDomains): bool
    {
        if (in_array($ip, ['127.0.0.1', '::1'], true) || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.')) {
            return true; // Local development bypass
        }

        $cacheKey = 'crawldns_'.md5($ip);

        return (bool) Cache::remember($cacheKey, 86400, function () use ($ip, $allowedDomains) {
            $hostname = @gethostbyaddr($ip);

            if ($hostname === false || $hostname === $ip) {
                return false;
            }

            $matchedDomain = false;
            foreach ($allowedDomains as $domain) {
                if (str_ends_with(strtolower($hostname), $domain)) {
                    $matchedDomain = true;
                    break;
                }
            }

            if (! $matchedDomain) {
                return false;
            }

            // Forward lookup: resolve hostname back to IP to prevent spoofed PTR
            $forwardIps = @gethostbynamel($hostname);
            if (! is_array($forwardIps)) {
                return false;
            }

            return in_array($ip, $forwardIps, true);
        });
    }

    /**
     * Determine if current request is targeting a public marketing / indexable route.
     */
    private function isPublicMarketingRoute(Request $request): bool
    {
        return ! $request->is('admin*')
            && ! $request->is('login*')
            && ! $request->is('register*')
            && ! $request->is('api*')
            && ! $request->is('customer*');
    }
}

