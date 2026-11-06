<?php

declare(strict_types=1);

namespace App\Domain\Shared;

final class ReservedSlugService
{
    /**
     * Complete registry of reserved root URL slugs and platform system prefixes.
     * Any tenant trying to use these slugs will have their slug sanitized (e.g. 'login-store').
     *
     * @var array<string>
     */
    public const RESERVED_SLUGS = [
        // 1. Core Authentication & Account
        'login',
        'logout',
        'register',
        'auth',
        'account-recovery',
        'forgot-password',
        'reset-password',
        'complete-profile',
        'select-business',
        'sanctum',
        'password',
        'email',
        'oauth',
        'sso',

        // 2. Platform Admin & Operations
        'admin',
        'superadmin',
        'backoffice',
        'ops',
        'api',
        'app',
        'up',
        '_laravel-brain',
        'mcp',
        'docs',

        // 3. Modules & Workspaces
        'dashboard',
        'pos',
        'hrm',
        'billing',
        'finance',
        'inventory',
        'sales',
        'purchasing',
        'purchase-orders',
        'invoices',
        'suppliers',
        'customers',
        'customer',
        'crm',
        'products',
        'product-categories',
        'services',
        'warehouse',
        'materials',
        'material-categories',
        'material-unit-conversions',
        'machines',
        'labor-rates',
        'labor-machines',
        'bom-headers',
        'bom-items',
        'units',
        'unit-conversions',
        'reports',
        'profitability',
        'tax',
        'import',
        'settings',
        'roles',
        'profile',
        'social-media',
        'whatsapp',
        'storefront',
        'settlements',
        'payments',
        'feedback',
        'community',
        'patungan',
        'b',

        // 4. Public Web, Lead Capture, Marketing & Legal
        'cooca',
        'cooca-indonesia',
        'jelajah',
        'direktori',
        'kalkulator',
        'kalkulator-hpp',
        'kalkulator-bep',
        'kalkulator-harga-jual',
        'kalkulator-laba-bersih',
        'kalkulator-gaji-karyawan',
        'kalkulator-pph-final',
        'kalkulator-omzet-harian',
        'simulasi-what-if',
        'simulator',
        'calculator',
        'template',
        'templates',
        'template-pembukuan-gratis',
        'solusi',
        'solutions',
        'blog',
        'kontak',
        'contact',
        'sitemap',
        'sitemap.xml',
        'sitemap_pages.xml',
        'sitemap_blog.xml',
        'sitemap_marketplace.xml',
        'sitemap_tools.xml',
        'robots.txt',
        'privacy',
        'kebijakan-privasi',
        'terms',
        'syarat-ketentuan',
        'receipt',
        'payslip',
        'landing-page',
        'onboarding',
        'businesses',
        'storage',
        'public',
        'assets',
        'css',
        'js',
        'images',
        'static',
        'webhook',
        'webhooks',
        't',
        'order',
        'cart',
        'checkout',

        // 5. Core Public Marketing Ecosystem & Subpages
        'about',
        'tentang',
        'pricing',
        'harga',
        'demo',
        'support',
        'bantuan',
        'security',
        'keamanan',
        'marketplace',
        'business-operating-system',
        'omnichannel-erp',
        'omnichannel',
        'content-automation',
        'resources',
        'download',
        'track',
        'geo',
        'live-chat',
        'data-deletion',
        'penghapusan-data',
        'aplikasi-kasir-gratis',
        'aplikasi-kasir-android',
        'aplikasi-kasir-offline',
        'integrations',
    ];

    /**
     * Get route regex pattern for negative lookahead matching.
     * Prevents any storefront route from capturing platform reserved paths.
     */
    public static function getRouteRegex(): string
    {
        // Extract slugs that are path segments (no file extensions like .xml)
        $cleanSlugs = array_values(array_filter(
            self::RESERVED_SLUGS,
            fn (string $s): bool => ! str_contains($s, '.')
        ));

        // Deduplicate and escape
        $unique = array_values(array_unique($cleanSlugs));
        $escaped = array_map(fn (string $s): string => preg_quote($s, '/'), $unique);
        $joined = implode('|', $escaped);

        return '^(?!(' . $joined . ')$)[a-z0-9]+(?:-[a-z0-9]+)*$';
    }

    /**
     * Check whether a given slug collides with a reserved platform keyword.
     */
    public static function isReserved(string $slug): bool
    {
        $normalized = strtolower(trim($slug));

        return in_array($normalized, self::RESERVED_SLUGS, true);
    }

    /**
     * Sanitize a slug if it is reserved by appending a safe suffix (e.g. 'admin' -> 'admin-store').
     */
    public static function sanitize(string $slug, string $suffix = 'store'): string
    {
        $normalized = strtolower(trim($slug));

        if (! self::isReserved($normalized)) {
            return $normalized;
        }

        return "{$normalized}-{$suffix}";
    }

    /**
     * Get all reserved slugs.
     *
     * @return array<string>
     */
    public static function all(): array
    {
        return self::RESERVED_SLUGS;
    }
}
