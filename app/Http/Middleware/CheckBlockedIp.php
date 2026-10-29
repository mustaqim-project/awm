<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Security\BruteForceProtectionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CheckBlockedIp
{
    public function __construct(
        private readonly BruteForceProtectionService $bruteForceService = new BruteForceProtectionService()
    ) {}

    /**
     * Handle an incoming request and block IPs flagged for brute-force attacks.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = (string) $request->ip();

        // Never block webhooks, health checks, or verified search/social crawlers
        if ($request->attributes->get('is_verified_crawler') === true
            || $request->is('up')
            || $request->is('payments/*')
            || $request->is('tripay/*')
            || $request->is('api/v1/wa/*')
            || $request->is('webhooks/*')) {
            return $next($request);
        }

        if ($this->bruteForceService->isIpBlocked($ip)) {
            $remainingMinutes = $this->bruteForceService->getRemainingBlockMinutes($ip);
            $message = "Alamat IP Anda ({$ip}) diblokir sementara karena terdeteksi aktivitas percobaan login berulang kali. Silakan coba lagi dalam {$remainingMinutes} menit.";

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => 'IP_TEMPORARILY_BLOCKED',
                    'message' => $message,
                    'remaining_minutes' => $remainingMinutes,
                ], 429);
            }

            if ($request->hasSession() && $request->isMethod('POST')) {
                return back()->withErrors([
                    'email' => $message,
                    'login' => $message,
                ])->withInput();
            }

            return response()->view('errors.429', [
                'message' => $message,
                'remainingMinutes' => $remainingMinutes,
            ], 429);
        }

        return $next($request);
    }
}
