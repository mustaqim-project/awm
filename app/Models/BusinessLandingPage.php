<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessLandingPage extends Model
{
    use BelongsToBusiness, HasFactory;

    protected $table = 'business_landing_pages';

    protected $fillable = [
        'business_id',
        'is_published',
        'industry_preset',
        'theme_preset',
        'active_pages',
        'custom_labels',
        'theme_color',
        'font_family',
        'dark_mode',
        'headline',
        'subheadline',
        'announcement_badge',
        'hero_image_url',
        'logo_url',
        'cta_primary_text',
        'cta_primary_url',
        'cta_secondary_text',
        'cta_secondary_url',
        'about_title',
        'about_story',
        'about_image_url',
        'values',
        'operational_hours',
        'show_pos_products',
        'services_title',
        'services_subtitle',
        'custom_services',
        'gallery_images',
        'gallery_title',
        'gallery_subtitle',
        'section_visibility',
        'testimonials',
        'faqs',
        'stats',
        'social_links',
        'google_maps_embed_url',
        'custom_phone',
        'custom_email',
        'whatsapp_number',
        'whatsapp_welcome_message',
        'instagram_handle',
        'tiktok_handle',
        'facebook_url',
        'custom_address',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image_url',
        'footer_description',
        'footer_navigation_title',
        'footer_services_title',
        'footer_contact_title',
        'footer_cta_text',
        'footer_copyright',
        'popup_enabled',
        'popup_title',
        'popup_badge',
        'popup_content',
        'popup_image_path',
        'popup_cta_text',
        'popup_cta_url',
        'popup_frequency',
        'popup_starts_at',
        'popup_ends_at',
    ];

    protected $casts = [
        'is_published'       => 'boolean',
        'dark_mode'          => 'boolean',
        'show_pos_products'  => 'boolean',
        'values'             => 'array',
        'operational_hours'  => 'array',
        'custom_services'    => 'array',
        'gallery_images'     => 'array',
        'testimonials'       => 'array',
        'faqs'               => 'array',
        'stats'              => 'array',
        'social_links'       => 'array',
        'section_visibility' => 'array',
        'active_pages'       => 'array',
        'custom_labels'      => 'array',
        'popup_enabled'      => 'boolean',
        'popup_starts_at'    => 'datetime',
        'popup_ends_at'      => 'datetime',
    ];

    /**
     * Default page visibility states if not explicitly set.
     */
    public function getActivePages(): array
    {
        $defaults = [
            'home'           => true,
            'catalog'        => true,
            'about'          => true,
            'reservation'    => false,
            'contact'        => true,
            'blog'           => false,
            'order_tracking' => true,
        ];

        return array_merge($defaults, (array) ($this->active_pages ?? []));
    }

    /**
     * Check if a specific standalone page is currently active.
     */
    public function isPageActive(string $page): bool
    {
        $activePages = $this->getActivePages();
        return (bool) ($activePages[$page] ?? false);
    }

    /**
     * Default section visibility states for universal storefront.
     * All 12 core sections are enabled by default.
     *
     * @var array<string, bool>
     */
    public const DEFAULT_SECTION_VISIBILITY = [
        'hero'            => true,
        'features_banner' => true,
        'category_chips'  => true,
        'products'        => true,
        'services'        => true,
        'gallery'         => true,
        'testimonials'    => true,
        'faq'             => true,
        'stats'           => true,
        'about'           => true,
        'contact'         => true,
        'footer'          => true,
    ];

    /**
     * Retrieve the merged section visibility array with default fallbacks.
     *
     * @return array<string, bool>
     */
    public function getSectionVisibility(): array
    {
        $visibility = $this->section_visibility;
        if (is_string($visibility)) {
            $decoded = json_decode($visibility, true);
            $visibility = is_array($decoded) ? $decoded : [];
        }

        return array_merge(self::DEFAULT_SECTION_VISIBILITY, (array) ($visibility ?? []));
    }

    /**
     * Check if a specific storefront section should be displayed.
     *
     * @param string $section Section identifier (e.g. 'hero', 'features_banner', 'products')
     * @param bool $default Fallback value if section is not explicitly configured
     * @return bool
     */
    public function isSectionVisible(string $section, bool $default = true): bool
    {
        $visibility = $this->section_visibility;
        if (is_string($visibility)) {
            $decoded = json_decode($visibility, true);
            $visibility = is_array($decoded) ? $decoded : [];
        }

        if (is_array($visibility)) {
            // 1. Direct key match
            if (array_key_exists($section, $visibility)) {
                return (bool) $visibility[$section];
            }

            // 2. Legacy / alternate alias match
            $alias = $this->resolveSectionAlias($section);
            if ($alias !== null && array_key_exists($alias, $visibility)) {
                return (bool) $visibility[$alias];
            }
        }

        // 3. Constant fallback or caller-supplied default
        if (array_key_exists($section, self::DEFAULT_SECTION_VISIBILITY)) {
            return (func_num_args() > 1) ? $default : self::DEFAULT_SECTION_VISIBILITY[$section];
        }

        return $default;
    }

    /**
     * Resolve legacy or alternative section key aliases.
     */
    private function resolveSectionAlias(string $section): ?string
    {
        $aliases = [
            'products'          => 'featured_products',
            'featured_products' => 'products',
            'category_chips'    => 'catalog_filter',
            'catalog_filter'    => 'category_chips',
            'faq'               => 'faqs',
            'faqs'              => 'faq',
            'testimonials'      => 'reviews',
            'reviews'           => 'testimonials',
            'about'             => 'about_story',
            'about_story'       => 'about',
        ];

        return $aliases[$section] ?? null;
    }

    /**
     * Retrieve normalized 4 universal value proposition pillars.
     * Guarantees exactly 4 items with both 'desc' and 'description' keys.
     *
     * @return array<int, array{icon: string, title: string, desc: string, description: string}>
     */
    public function getUniversalValues(): array
    {
        $rawValues = $this->values;
        if (is_string($rawValues)) {
            $decoded = json_decode($rawValues, true);
            $rawValues = is_array($decoded) ? $decoded : [];
        }
        if (! is_array($rawValues)) {
            $rawValues = [];
        }

        // Filter only sequential/numeric elements to exclude legacy structures like 'stats'
        $cmsItems = [];
        foreach ($rawValues as $key => $item) {
            if (is_numeric($key) && is_array($item)) {
                $cmsItems[] = $item;
            }
        }

        $defaultPillars = self::getDefaultUniversalValues();

        $normalized = [];
        for ($i = 0; $i < 4; $i++) {
            $userItem = $cmsItems[$i] ?? null;
            $default = $defaultPillars[$i];

            $icon = ! empty($userItem['icon']) ? (string) $userItem['icon'] : $default['icon'];
            $title = ! empty($userItem['title']) ? (string) $userItem['title'] : $default['title'];

            $rawDesc = ! empty($userItem['desc'])
                ? (string) $userItem['desc']
                : (! empty($userItem['description']) ? (string) $userItem['description'] : '');

            $desc = $rawDesc !== '' ? $rawDesc : $default['desc'];

            $normalized[] = [
                'icon'        => $icon,
                'title'       => $title,
                'desc'        => $desc,
                'description' => $desc,
            ];
        }

        return $normalized;
    }

    /**
     * Get default 4 universal pillars with i18n translation support and safe static fallbacks.
     *
     * @return array<int, array{icon: string, title: string, desc: string, description: string}>
     */
    public static function getDefaultUniversalValues(): array
    {
        $hasI18n = function (string $key): bool {
            return function_exists('trans') && trans()->has($key);
        };

        $t = function (string $key, string $fallback) use ($hasI18n): string {
            return $hasI18n($key) ? (string) __($key) : $fallback;
        };

        $p1Desc = $t('storefront.features.official_desc', 'Jaminan produk original dengan standar kualitas terbaik.');
        $p2Desc = $t('storefront.features.payment_desc', 'Mendukung QRIS, transfer bank, dan aneka metode pembayaran aman.');
        $p3Desc = $t('storefront.features.delivery_desc', 'Pesanan diproses segera dan dikirim tepat waktu dengan aman.');
        $p4Desc = $t('storefront.features.care_desc', 'Tim ramah dan responsif siap melayani konsultasi serta kebutuhan Anda.');

        return [
            [
                'icon'        => 'shield-check',
                'title'       => $t('storefront.features.official_store', 'Toko Resmi Terpercaya'),
                'desc'        => $p1Desc,
                'description' => $p1Desc,
            ],
            [
                'icon'        => 'credit-card',
                'title'       => $t('storefront.features.easy_payment', 'Transaksi Aman & Mudah'),
                'desc'        => $p2Desc,
                'description' => $p2Desc,
            ],
            [
                'icon'        => 'truck',
                'title'       => $t('storefront.features.secure_delivery', 'Layanan Cepat & Terpercaya'),
                'desc'        => $p3Desc,
                'description' => $p3Desc,
            ],
            [
                'icon'        => 'headset',
                'title'       => $t('storefront.features.customer_care', 'Dukungan Pelanggan Prima'),
                'desc'        => $p4Desc,
                'description' => $p4Desc,
            ],
        ];
    }

    /**
     * Retrieve custom navigation label for a page, falling back to default.
     */
    public function getNavLabel(string $page, string $default): string
    {
        $labels = (array) ($this->custom_labels ?? []);
        return ! empty($labels[$page]) ? (string) $labels[$page] : $default;
    }

    /**
     * Get theme preset string or fallback.
     */
    public function getThemePreset(): string
    {
        if (! empty($this->theme_preset)) {
            return $this->theme_preset;
        }

        $business = $this->relationLoaded('business') ? $this->business : $this->business()->first();
        if ($business) {
            $detected = app(\App\Domain\Storefront\StorefrontThemeService::class)->detectThemeKeyForBusiness($business);
            if ($detected) {
                return $detected;
            }
        }

        if (! empty($this->industry_preset)) {
            return $this->industry_preset;
        }

        return 'modern_umkm';
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Generate the direct WhatsApp ordering URL with pre-filled greeting.
     */
    public function getWhatsAppUrl(?string $serviceName = null): string
    {
        $phone = preg_replace('/[^0-9]/', '', $this->whatsapp_number ?: $this->custom_phone ?: $this->business->phone ?: '');

        if (! $phone) {
            return '#';
        }

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        $msg = $this->whatsapp_welcome_message;

        if ($serviceName) {
            $msg = "Halo {$this->business->name}, saya ingin memesan / menanyakan mengenai: *{$serviceName}*. Apakah masih tersedia?";
        }

        return $msg ? "https://wa.me/{$phone}?text=" . rawurlencode($msg) : "https://wa.me/{$phone}";
    }

    /**
     * Return operational hours normalized to a standard list:
     * [['day' => string, 'hours' => string, 'is_open' => bool], ...]
     */
    public function getNormalizedOperationalHours(): array
    {
        $hours = $this->operational_hours;
        if (empty($hours) || !is_array($hours)) {
            return [];
        }

        $dayTranslations = [
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu',
            'sunday' => 'Minggu',
        ];

        $normalized = [];
        foreach ($hours as $key => $item) {
            if (!is_array($item)) {
                if (is_string($item)) {
                    $normalized[] = [
                        'day' => is_string($key) ? ucfirst($key) : 'Hari',
                        'hours' => $item,
                        'is_open' => strtolower($item) !== 'tutup',
                    ];
                }
                continue;
            }

            if (isset($item['day'])) {
                $normalized[] = [
                    'day' => (string) $item['day'],
                    'hours' => (string) ($item['hours'] ?? ''),
                    'is_open' => !empty($item['is_open']),
                ];
                continue;
            }

            $dayKey = is_string($key) ? strtolower($key) : '';
            $dayName = $dayTranslations[$dayKey] ?? (is_string($key) && !is_numeric($key) ? ucfirst($key) : 'Hari ' . ($key + 1));
            $isOpen = !empty($item['is_open']) || (!empty($item['open']) && $item['open'] !== 'closed');
            $openTime = $item['open'] ?? '';
            $closeTime = $item['close'] ?? '';
            $hoursText = $item['hours'] ?? ($isOpen && $openTime && $closeTime ? "{$openTime} - {$closeTime} WIB" : ($isOpen ? 'Buka' : 'Tutup'));

            $normalized[] = [
                'day' => $dayName,
                'hours' => $hoursText,
                'is_open' => $isOpen,
            ];
        }

        return $normalized;
    }

    public function getHeroImageUrlAttribute(?string $value): ?string
    {
        return $this->normalizeImageUrl($value);
    }

    public function getLogoUrlAttribute(?string $value): ?string
    {
        return $this->normalizeImageUrl($value);
    }

    public function getAboutImageUrlAttribute(?string $value): ?string
    {
        return $this->normalizeImageUrl($value);
    }

    public function getOgImageUrlAttribute(?string $value): ?string
    {
        return $this->normalizeImageUrl($value);
    }

    public function getGalleryImagesAttribute($value): array
    {
        $images = is_string($value) ? json_decode($value, true) : $value;
        if (!is_array($images)) {
            return [];
        }

        return array_map(function ($item) {
            if (is_array($item)) {
                if (isset($item['url'])) {
                    $item['url'] = $this->normalizeImageUrl($item['url']);
                }
                return $item;
            }
            return $this->normalizeImageUrl((string) $item);
        }, $images);
    }

    public function getCustomServicesAttribute($value): array
    {
        $services = is_string($value) ? json_decode($value, true) : $value;
        if (!is_array($services)) {
            return [];
        }

        return array_map(function ($item) {
            if (is_array($item) && isset($item['image_url'])) {
                $item['image_url'] = $this->normalizeImageUrl($item['image_url']);
            }
            return $item;
        }, $services);
    }

    private function normalizeImageUrl(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        // If it's an absolute URL pointing to any domain's storage/ path (e.g. https://cooca.id/storage/...)
        if (preg_match('#^https?://[^/]+/storage/(.*)$#i', $url, $matches)) {
            return asset('storage/' . $matches[1]);
        }

        if (str_starts_with($url, 'storage/') || str_starts_with($url, '/storage/')) {
            return asset(ltrim($url, '/'));
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return asset('storage/' . ltrim($url, '/'));
    }

    /**
     * Determine whether the promotional pop-up should be displayed.
     */
    public function isPopupActive(): bool
    {
        if (! $this->popup_enabled || blank($this->popup_title)) {
            return false;
        }

        $now = now();
        if ($this->popup_starts_at && $now->isBefore($this->popup_starts_at)) {
            return false;
        }

        if ($this->popup_ends_at && $now->isAfter($this->popup_ends_at)) {
            return false;
        }

        return true;
    }

    /**
     * Get the public URL for the pop-up banner image.
     */
    public function getPopupImageUrlAttribute(): ?string
    {
        if (empty($this->popup_image_path)) {
            return null;
        }

        return \App\Domain\Storage\TenantStorage::url($this->popup_image_path);
    }
}
