<?php

declare(strict_types=1);

namespace App\Domain\Ai\Studio\Security;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class SsrfProtectionService
{
    /**
     * Hostname internal/privat yang mutlak dilarang.
     */
    protected const BLOCKED_HOSTS = [
        'localhost',
        'metadata.google.internal',
        'metadata',
        'instance-data',
        '169.254.169.254',
        'kubernetes.default',
        'kubernetes.default.svc',
    ];

    /**
     * Port yang diizinkan untuk fetch gambar publik.
     */
    protected const ALLOWED_PORTS = [80, 443, 8080, 8443];

    /**
     * Validasi keamanan URL dari celah SSRF.
     *
     * @throws InvalidArgumentException jika URL berbahaya atau mengarah ke resource privat
     */
    public function validateUrl(string $url): string
    {
        if (trim($url) === '') {
            throw new InvalidArgumentException('URL tidak boleh kosong.');
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException("Format URL tidak valid: {$url}");
        }

        $parsed = parse_url($url);
        if ($parsed === false || empty($parsed['scheme']) || empty($parsed['host'])) {
            throw new InvalidArgumentException('Gagal mem-parsing komponen URL.');
        }

        // 1. Skema wajib HTTP atau HTTPS
        $scheme = strtolower((string) $parsed['scheme']);
        if (! in_array($scheme, ['http', 'https'], true)) {
            throw new InvalidArgumentException("Protokol '{$scheme}' tidak diizinkan. Hanya HTTP dan HTTPS yang diperbolehkan.");
        }

        // 2. Batasi port jika ditentukan
        if (isset($parsed['port']) && ! in_array((int) $parsed['port'], self::ALLOWED_PORTS, true)) {
            throw new InvalidArgumentException("Port '{$parsed['port']}' diblokir oleh kebijakan keamanan SSRF.");
        }

        $host = strtolower((string) $parsed['host']);

        // 3. Cek hostname blacklist & domain internal
        if (in_array($host, self::BLOCKED_HOSTS, true) || str_ends_with($host, '.internal') || str_ends_with($host, '.local') || str_ends_with($host, '.lan')) {
            throw new InvalidArgumentException("Host privat/internal '{$host}' diblokir oleh proteksi SSRF.");
        }

        // 4. Resolusi IP address melalui DNS
        $ip = gethostbyname($host);

        // Jika gethostbyname gagal meresolusi dan mengembalikan host yang sama, cek apakah host adalah IP mentah
        if ($ip === $host && ! filter_var($host, FILTER_VALIDATE_IP)) {
            throw new InvalidArgumentException("Domain '{$host}' tidak dapat diresolusi ke alamat IP publik.");
        }

        // 5. Cek apakah IP adalah loopback, privat, atau link-local
        if ($this->isPrivateOrReservedIp($ip)) {
            throw new InvalidArgumentException("Akses ke rentang IP privat atau reserved ({$ip}) diblokir oleh proteksi SSRF.");
        }

        return $url;
    }

    /**
     * Cek apakah URL aman tanpa melempar exception.
     */
    public function isSafe(string $url): bool
    {
        try {
            $this->validateUrl($url);

            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    /**
     * Lakukan GET request yang aman dengan SSRF filtering ketat.
     */
    public function safeGet(string $url, int $timeoutSeconds = 30): Response
    {
        $safeUrl = $this->validateUrl($url);

        return Http::timeout($timeoutSeconds)
            ->withOptions(['allow_redirects' => false]) // Cegah SSRF via redirect ke internal host
            ->get($safeUrl);
    }

    /**
     * Cek apakah IP merupakan rentang privat, link-local, loopback, atau reserved.
     */
    public function isPrivateOrReservedIp(string $ip): bool
    {
        // 1. Cek bawaan filter_var (FILTER_FLAG_NO_PRIV_RANGE & FILTER_FLAG_NO_RES_RANGE)
        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return true;
        }

        // 2. Pemeriksaan eksplisit untuk IPv4
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            // Loopback (127.0.0.0/8)
            if (str_starts_with($ip, '127.')) {
                return true;
            }

            // Private RFC1918: 10.0.0.0/8
            if (str_starts_with($ip, '10.')) {
                return true;
            }

            // Private RFC1918: 172.16.0.0/12 (172.16.x.x - 172.31.x.x)
            if (preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $ip)) {
                return true;
            }

            // Private RFC1918: 192.168.0.0/16
            if (str_starts_with($ip, '192.168.')) {
                return true;
            }

            // Link-local / Cloud metadata: 169.254.0.0/16
            if (str_starts_with($ip, '169.254.')) {
                return true;
            }

            // Current network / 0.0.0.0/8
            if (str_starts_with($ip, '0.')) {
                return true;
            }
        }

        // 3. Pemeriksaan eksplisit untuk IPv6
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $lowerIp = strtolower($ip);
            if ($lowerIp === '::1' || str_starts_with($lowerIp, 'fe80:') || str_starts_with($lowerIp, 'fc') || str_starts_with($lowerIp, 'fd')) {
                return true;
            }
        }

        return false;
    }
}
