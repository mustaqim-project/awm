<?php

declare(strict_types=1);

namespace App\Domain\Ai\Inspiration\RightsPolicyService;

namespace App\Domain\Ai\Inspiration;

use App\Domain\Ai\Inspiration\Exceptions\CommercialUseNotAllowedException;
use App\Models\AiStudio\InspirationItem;
use App\Models\Business;

class RightsPolicyService
{
    /**
     * Daftar lisensi yang diizinkan untuk pemrosesan AI Studio & promosi komersial.
     *
     * @var array<int, string>
     */
    protected const ALLOWED_COMMERCIAL_LICENSES = [
        'cc0',
        'public_domain',
        'pexels',
        'pexels_license',
        'unsplash',
        'unsplash_license',
        'pixabay',
        'pixabay_license',
        'cc_by',
        'cc-by',
        'cc_by_sa',
        'cc-by-sa',
        'commercial_allowed',
        'royalty_free',
    ];

    /**
     * Daftar lisensi non-komersial / terlarang.
     *
     * @var array<int, string>
     */
    protected const PROHIBITED_LICENSES = [
        'cc_by_nc',
        'cc-by-nc',
        'cc_by_nc_sa',
        'cc-by-nc-sa',
        'cc_by_nc_nd',
        'cc-by-nc-nd',
        'all_rights_reserved',
        'editorial_only',
        'restricted',
        'unknown',
    ];

    /**
     * Evaluasi biner: Cek apakah referensi boleh digunakan secara komersial.
     */
    public function canUseCommercially(InspirationItem|array|string $licenseOrItem): bool
    {
        $license = $this->resolveLicenseName($licenseOrItem);

        if ($license === '') {
            return false;
        }

        $normalized = strtolower(str_replace([' ', '-'], '_', $license));

        foreach (self::PROHIBITED_LICENSES as $prohibited) {
            $normProhibited = str_replace('-', '_', $prohibited);
            if (str_contains($normalized, $normProhibited)) {
                return false;
            }
        }

        foreach (self::ALLOWED_COMMERCIAL_LICENSES as $allowed) {
            $normAllowed = str_replace('-', '_', $allowed);
            if (str_contains($normalized, $normAllowed)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validasi tegas: Lempar exception jika lisensi tidak memenuhi syarat komersial.
     *
     * @throws CommercialUseNotAllowedException
     */
    public function assertCanUseCommercially(InspirationItem|array|string $licenseOrItem): void
    {
        if (! $this->canUseCommercially($licenseOrItem)) {
            $license = $this->resolveLicenseName($licenseOrItem);
            throw new CommercialUseNotAllowedException($license !== '' ? $license : 'Tidak Diketahui');
        }
    }

    /**
     * Format teks atribusi legal standar sesuai klausul lisensi sumber.
     */
    public function formatAttribution(InspirationItem|array $item): string
    {
        $photographer = 'Kreator';
        $license = 'Lisensi Bebas Komersial';
        $sourceUrl = '';

        if ($item instanceof InspirationItem) {
            $notes = (string) ($item->curated_notes ?? '');
            $license = (string) ($item->source->name ?? 'Koleksi Inspirasi');
            $sourceUrl = (string) ($item->canonical_url ?? '');

            if (preg_match('/foto oleh\s+([^,]+)/i', $notes, $m)) {
                $photographer = trim($m[1]);
            }
        } else {
            $photographer = (string) ($item['photographer_name'] ?? $item['photographer'] ?? 'Kreator');
            $license = (string) ($item['license'] ?? 'Lisensi Bebas Komersial');
            $sourceUrl = (string) ($item['canonical_url'] ?? $item['url'] ?? '');
        }

        $text = "Gaya visual terinspirasi dari karya visual oleh {$photographer} ({$license})";
        if ($sourceUrl !== '') {
            $text .= ". Sumber: {$sourceUrl}";
        }

        return $text;
    }

    /**
     * Disclaimer hukum untuk perlindungan merchant dan platform.
     */
    public function getDisclaimerNotice(InspirationItem|array $item, Business $business): string
    {
        $attribution = $this->formatAttribution($item);
        $brandName = (string) ($business->name ?? 'Merchant');

        return "{$attribution}. Gambar akhir merupakan hasil rekayasa visual AI yang menampilkan produk riil resmi milik {$brandName}. Seluruh hak merek dagang dan hak cipta produk dilindungi undang-undang.";
    }

    /**
     * Resolusi nama lisensi dari model, array, atau string.
     */
    protected function resolveLicenseName(InspirationItem|array|string $licenseOrItem): string
    {
        if (is_string($licenseOrItem)) {
            return trim($licenseOrItem);
        }

        if ($licenseOrItem instanceof InspirationItem) {
            if ($licenseOrItem->source !== null && $licenseOrItem->source->policy !== null) {
                if ($licenseOrItem->source->policy->allow_commercial_use) {
                    return 'commercial_allowed';
                }
            }

            $curatedNotes = (string) ($licenseOrItem->curated_notes ?? '');
            if (preg_match('/lisensi\s*:\s*([^,\n]+)/i', $curatedNotes, $matches)) {
                return trim($matches[1]);
            }

            return 'commercial_allowed';
        }

        return (string) ($licenseOrItem['license'] ?? $licenseOrItem['license_name'] ?? '');
    }
}
