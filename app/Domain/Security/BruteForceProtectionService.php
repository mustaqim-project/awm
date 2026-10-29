<?php

declare(strict_types=1);

namespace App\Domain\Security;

use App\Models\AuditLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BruteForceProtectionService
{
    // Thresholds
    public const MAX_ACCOUNT_ATTEMPTS = 5;       // 5 consecutive failed attempts on same account triggers lockout & IP ban
    public const ACCOUNT_LOCKOUT_SECONDS = 900;  // 15 minutes lockout for IP + Account
    public const MAX_IP_TOTAL_ATTEMPTS = 10;     // 10 failed attempts from same IP across accounts
    public const IP_BLOCK_SECONDS = 3600;        // 60 minutes IP block on brute-force attack

    /**
     * Check if an IP address is currently blocked.
     */
    public function isIpBlocked(string $ip): bool
    {
        if ($this->isWhitelisted($ip)) {
            return false;
        }

        return (bool) Cache::has("security:blocked_ip:{$ip}");
    }

    /**
     * Get remaining block time in minutes for a blocked IP.
     */
    public function getRemainingBlockMinutes(string $ip): int
    {
        $meta = Cache::get("security:blocked_ip_meta:{$ip}");
        if (! is_array($meta) || empty($meta['blocked_until'])) {
            return (int) ceil(self::IP_BLOCK_SECONDS / 60);
        }

        $blockedUntil = Carbon::parse($meta['blocked_until']);
        $diff = now()->diffInMinutes($blockedUntil, false);

        return max(1, (int) ceil($diff));
    }

    /**
     * Check if an account is locked out from a specific IP.
     */
    public function isAccountLocked(string $ip, string $identifier): bool
    {
        if ($this->isWhitelisted($ip)) {
            return false;
        }

        $normalized = $this->normalizeIdentifier($identifier);

        return (bool) Cache::has("security:account_lock:{$ip}:{$normalized}");
    }

    /**
     * Get remaining lockout time in minutes for IP + Account.
     */
    public function getRemainingAccountLockoutMinutes(string $ip, string $identifier): int
    {
        $normalized = $this->normalizeIdentifier($identifier);
        $expiresAt = Cache::get("security:account_lock_until:{$ip}:{$normalized}");
        if (! $expiresAt) {
            return (int) ceil(self::ACCOUNT_LOCKOUT_SECONDS / 60);
        }

        $diff = now()->diffInMinutes(Carbon::parse($expiresAt), false);

        return max(1, (int) ceil($diff));
    }

    /**
     * Record a failed login attempt and evaluate brute force / IP blocking triggers.
     *
     * @return array{
     *     is_blocked: bool,
     *     is_account_locked: bool,
     *     remaining_minutes: int,
     *     attempts_on_account: int,
     *     attempts_on_ip: int
     * }
     */
    public function recordFailedAttempt(string $ip, string $identifier, string $portal = 'web'): array
    {
        if ($this->isWhitelisted($ip)) {
            return [
                'is_blocked' => false,
                'is_account_locked' => false,
                'remaining_minutes' => 0,
                'attempts_on_account' => 0,
                'attempts_on_ip' => 0,
            ];
        }

        $normalized = $this->normalizeIdentifier($identifier);

        // 1. Increment account-specific failed attempts from this IP
        $accountKey = "security:failed_login:ip:{$ip}:account:{$normalized}";
        $accountAttempts = (int) Cache::get($accountKey, 0) + 1;
        Cache::put($accountKey, $accountAttempts, now()->addSeconds(self::ACCOUNT_LOCKOUT_SECONDS));

        // 2. Increment global failed attempts from this IP
        $ipTotalKey = "security:failed_login:ip:{$ip}:total";
        $ipAttempts = (int) Cache::get($ipTotalKey, 0) + 1;
        Cache::put($ipTotalKey, $ipAttempts, now()->addSeconds(self::IP_BLOCK_SECONDS));

        $isAccountLocked = false;
        $isBlocked = false;
        $remainingMinutes = 0;

        // Condition A: 5 failed attempts on same account -> Lock account for this IP & block IP immediately
        if ($accountAttempts >= self::MAX_ACCOUNT_ATTEMPTS) {
            $isAccountLocked = true;
            $lockUntil = now()->addSeconds(self::ACCOUNT_LOCKOUT_SECONDS);
            Cache::put("security:account_lock:{$ip}:{$normalized}", true, $lockUntil);
            Cache::put("security:account_lock_until:{$ip}:{$normalized}", $lockUntil->toIso8601String(), $lockUntil);

            // Per User Requirement: Blokir IP jika mencoba login ke akun yang sama berkali-kali
            $this->blockIp(
                ip: $ip,
                durationSeconds: self::IP_BLOCK_SECONDS,
                reason: "Percobaan login berulang kali ke akun '{$normalized}' gagal {$accountAttempts}x (Brute Force Attack)",
                portal: $portal,
                identifier: $normalized
            );
            $isBlocked = true;
            $remainingMinutes = (int) ceil(self::IP_BLOCK_SECONDS / 60);
        }

        // Condition B: 10 failed attempts from this IP across any accounts -> Block IP
        if ($ipAttempts >= self::MAX_IP_TOTAL_ATTEMPTS && ! $isBlocked) {
            $this->blockIp(
                ip: $ip,
                durationSeconds: self::IP_BLOCK_SECONDS,
                reason: "Akumulasi kegagalan login {$ipAttempts}x dari IP ini melampaui ambang batas keamanan (Distributed Dictionary Attack)",
                portal: $portal,
                identifier: $normalized
            );
            $isBlocked = true;
            $remainingMinutes = (int) ceil(self::IP_BLOCK_SECONDS / 60);
        }

        return [
            'is_blocked' => $isBlocked,
            'is_account_locked' => $isAccountLocked,
            'remaining_minutes' => $remainingMinutes,
            'attempts_on_account' => $accountAttempts,
            'attempts_on_ip' => $ipAttempts,
        ];
    }

    /**
     * Block an IP address for a specific duration.
     */
    public function blockIp(
        string $ip,
        int $durationSeconds = self::IP_BLOCK_SECONDS,
        string $reason = 'Brute Force Attack Detected',
        string $portal = 'web',
        ?string $identifier = null
    ): void {
        if ($this->isWhitelisted($ip)) {
            return;
        }

        $until = now()->addSeconds($durationSeconds);

        Cache::put("security:blocked_ip:{$ip}", true, $until);
        Cache::put("security:blocked_ip_meta:{$ip}", [
            'ip' => $ip,
            'blocked_at' => now()->toIso8601String(),
            'blocked_until' => $until->toIso8601String(),
            'reason' => $reason,
            'portal' => $portal,
            'identifier' => $identifier,
            'user_agent' => request()?->userAgent(),
        ], $until);

        Log::warning("[BRUTE FORCE DEFENSE] IP {$ip} has been blocked until {$until->toIso8601String()}. Reason: {$reason}");

        // Record high risk security audit log
        try {
            AuditLog::create([
                'id' => (string) Str::uuid(),
                'business_id' => null,
                'user_id' => null,
                'auditable_type' => 'App\\Models\\SecurityBlock',
                'auditable_id' => (string) Str::uuid(),
                'action' => 'security.ip_blocked',
                'risk_level' => AuditLog::RISK_HIGH,
                'risk_reason' => $reason,
                'notes' => "IP {$ip} diblokir selama " . round($durationSeconds / 60) . " menit via portal {$portal} (Target: {$identifier}).",
                'ip_address' => $ip,
                'user_agent' => request()?->userAgent(),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error("[BruteForceProtectionService] Failed to record audit log: " . $e->getMessage());
        }
    }

    /**
     * Reset failed attempt counters upon successful authentication.
     */
    public function resetAttempts(string $ip, string $identifier): void
    {
        $normalized = $this->normalizeIdentifier($identifier);
        Cache::forget("security:failed_login:ip:{$ip}:account:{$normalized}");
        Cache::forget("security:account_lock:{$ip}:{$normalized}");
        Cache::forget("security:account_lock_until:{$ip}:{$normalized}");
    }

    /**
     * Manually unblock an IP address.
     */
    public function unblockIp(string $ip): void
    {
        Cache::forget("security:blocked_ip:{$ip}");
        Cache::forget("security:blocked_ip_meta:{$ip}");
        Cache::forget("security:failed_login:ip:{$ip}:total");
        Log::info("[BRUTE FORCE DEFENSE] IP {$ip} has been unblocked.");
    }

    /**
     * Check if an IP is whitelisted from brute force bans.
     */
    public function isWhitelisted(string $ip): bool
    {
        $whitelisted = array_filter(array_map('trim', explode(',', (string) config('security.whitelisted_ips', ''))));

        return in_array($ip, $whitelisted, true);
    }

    /**
     * Normalize account identifier (lowercased email or phone numbers).
     */
    public function normalizeIdentifier(string $identifier): string
    {
        return strtolower(trim($identifier));
    }
}

