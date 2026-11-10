<?php

declare(strict_types=1);

namespace App\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use InvalidArgumentException;

/**
 * Service for RFC 6238 Time-based One-Time Password (TOTP) / Google Authenticator.
 * Fully compatible with Google Authenticator, Microsoft Authenticator, Authy, and 1Password.
 */
class Google2FAService
{
    private const BASE32_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate a cryptographically secure Base32 secret key for Google Authenticator.
     */
    public function generateSecret(int $length = 16): string
    {
        $secret = '';
        $max = strlen(self::BASE32_CHARS) - 1;
        for ($i = 0; $i < $length; $i++) {
            $secret .= self::BASE32_CHARS[random_int(0, $max)];
        }

        return $secret;
    }

    /**
     * Calculate 6-digit TOTP code for a given secret at a timestamp.
     */
    public function getCode(string $secret, ?int $timestamp = null): string
    {
        $timestamp = $timestamp ?? time();
        $timeSlice = (int) floor($timestamp / 30);
        $secretKey = $this->base32Decode($secret);

        // Pack 64-bit integer into 8 bytes big-endian
        $binaryTime = pack('N*', 0) . pack('N*', $timeSlice);

        // Calculate HMAC-SHA1
        $hash = hash_hmac('sha1', $binaryTime, $secretKey, true);

        // Dynamic truncation (RFC 4226)
        $offset = ord(substr($hash, -1)) & 0x0F;
        $part = substr($hash, $offset, 4);
        $unpacked = unpack('N', $part);
        $value = ($unpacked[1] & 0x7FFFFFFF) % 1000000;

        return str_pad((string) $value, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Verify user provided 6-digit TOTP code against secret.
     * Uses discrepancy window (default 1 = ±30 seconds) to tolerate clock drift.
     */
    public function verifyCode(string $secret, string $code, int $discrepancy = 1): bool
    {
        $cleanCode = trim(str_replace([' ', '-'], '', $code));
        if (strlen($cleanCode) !== 6 || ! ctype_digit($cleanCode)) {
            return false;
        }

        $currentTime = time();
        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $checkTime = $currentTime + ($i * 30);
            $calculatedCode = $this->getCode($secret, $checkTime);
            if (hash_equals($calculatedCode, $cleanCode)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate otpauth:// URI for QR code scanning.
     */
    public function getOtpAuthUri(string $company, string $holder, string $secret): string
    {
        return 'otpauth://totp/' . rawurlencode($company) . ':' . rawurlencode($holder) .
            '?secret=' . rawurlencode($secret) .
            '&issuer=' . rawurlencode($company);
    }

    /**
     * Generate SVG QR Code markup for display in admin profile or modal.
     */
    public function getQrCodeSvg(string $company, string $holder, string $secret, int $size = 200): string
    {
        $uri = $this->getOtpAuthUri($company, $holder, $secret);

        $renderer = new ImageRenderer(
            new RendererStyle($size, 1),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);

        return $writer->writeString($uri);
    }

    /**
     * Decode a Base32 string into binary bytes.
     */
    private function base32Decode(string $secret): string
    {
        $secret = strtoupper(trim(str_replace(' ', '', $secret)));
        $buffer = 0;
        $bitsLeft = 0;
        $output = '';

        for ($i = 0, $len = strlen($secret); $i < $len; $i++) {
            $char = $secret[$i];
            if ($char === '=') {
                continue;
            }
            $val = strpos(self::BASE32_CHARS, $char);
            if ($val === false) {
                throw new InvalidArgumentException("Invalid Base32 character encountered: {$char}");
            }
            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;
            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $output .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $output;
    }
}

