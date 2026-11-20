<?php

declare(strict_types=1);

namespace Tests\E2E\Support\Helpers;

use App\Models\Business;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final class TenantHelper
{
    /**
     * Map of standard aliases to candidate slugs and names.
     *
     * @var array<string, array<int, string>>
     */
    private static array $aliasMap = [
        'cooca_mart' => [
            'cooca-mart',
            'cooca_mart',
            'cooca-mart-indonesia',
            'serba-jaya-retail-mart',
            'shopee-reviewer-store',
        ],
        'cooca-mart' => [
            'cooca-mart',
            'cooca_mart',
            'cooca-mart-indonesia',
            'serba-jaya-retail-mart',
            'shopee-reviewer-store',
        ],
        'mart' => [
            'cooca-mart',
            'cooca_mart',
            'cooca-mart-indonesia',
            'serba-jaya-retail-mart',
            'shopee-reviewer-store',
        ],
        'retail' => [
            'cooca-mart',
            'cooca_mart',
            'cooca-mart-indonesia',
            'serba-jaya-retail-mart',
            'shopee-reviewer-store',
        ],
        'cooca_bistro' => [
            'cooca-bistro',
            'cooca_bistro',
            'cooca-bistro-coffee',
            'kopi-senja-utama',
            'restoran-nusantara-rasa',
        ],
        'cooca-bistro' => [
            'cooca-bistro',
            'cooca_bistro',
            'cooca-bistro-coffee',
            'kopi-senja-utama',
            'restoran-nusantara-rasa',
        ],
        'bistro' => [
            'cooca-bistro',
            'cooca_bistro',
            'cooca-bistro-coffee',
            'kopi-senja-utama',
            'restoran-nusantara-rasa',
        ],
        'fnb' => [
            'cooca-bistro',
            'cooca_bistro',
            'cooca-bistro-coffee',
            'kopi-senja-utama',
            'restoran-nusantara-rasa',
        ],
        'food_beverage' => [
            'cooca-bistro',
            'cooca_bistro',
            'cooca-bistro-coffee',
            'kopi-senja-utama',
            'restoran-nusantara-rasa',
        ],
        'cooca_mfg' => [
            'cooca-mfg',
            'cooca_mfg',
            'cooca-manufacturing',
            'cooca-mfg-indonesia',
            'presisi-baja-plastik',
            'citra-busana-konveksi',
        ],
        'cooca-mfg' => [
            'cooca-mfg',
            'cooca_mfg',
            'cooca-manufacturing',
            'cooca-mfg-indonesia',
            'presisi-baja-plastik',
            'citra-busana-konveksi',
        ],
        'mfg' => [
            'cooca-mfg',
            'cooca_mfg',
            'cooca-manufacturing',
            'cooca-mfg-indonesia',
            'presisi-baja-plastik',
            'citra-busana-konveksi',
        ],
        'manufacturing' => [
            'cooca-mfg',
            'cooca_mfg',
            'cooca-manufacturing',
            'cooca-mfg-indonesia',
            'presisi-baja-plastik',
            'citra-busana-konveksi',
        ],
        'cooca_autocare' => [
            'cooca-autocare',
            'cooca_autocare',
            'cooca-autocare-workshop',
            'garasi-otomotif-prima',
            'garasi-motor-jaya',
            'kilau-auto-detailing',
        ],
        'cooca-autocare' => [
            'cooca-autocare',
            'cooca_autocare',
            'cooca-autocare-workshop',
            'garasi-otomotif-prima',
            'garasi-motor-jaya',
            'kilau-auto-detailing',
        ],
        'autocare' => [
            'cooca-autocare',
            'cooca_autocare',
            'cooca-autocare-workshop',
            'garasi-otomotif-prima',
            'garasi-motor-jaya',
            'kilau-auto-detailing',
        ],
        'workshop' => [
            'cooca-autocare',
            'cooca_autocare',
            'cooca-autocare-workshop',
            'garasi-otomotif-prima',
            'garasi-motor-jaya',
            'kilau-auto-detailing',
        ],
    ];

    /**
     * Cache for resolved business IDs.
     *
     * @var array<string, string>
     */
    private static array $resolvedCache = [];

    /**
     * List of transient tenant IDs created during test runs.
     *
     * @var array<int, string>
     */
    private static array $transientTenantIds = [];

    /**
     * Retrieve a demo or registered business ID by alias, slug, or name.
     */
    public static function getBusinessId(string $alias): ?string
    {
        $normalizedKey = strtolower(str_replace(['-', ' '], '_', trim($alias)));

        if (isset(self::$resolvedCache[$normalizedKey])) {
            return self::$resolvedCache[$normalizedKey];
        }

        // 1. Direct UUID check
        if (Str::isUuid($alias)) {
            $exists = DB::table('businesses')->where('id', $alias)->exists();
            if ($exists) {
                return self::$resolvedCache[$normalizedKey] = $alias;
            }
        }

        // 2. Lookup by alias candidate slugs
        $candidates = self::$aliasMap[$normalizedKey]
            ?? self::$aliasMap[$alias]
            ?? [$alias, $normalizedKey];

        $found = DB::table('businesses')
            ->whereIn('slug', $candidates)
            ->value('id');

        if ($found !== null) {
            return self::$resolvedCache[$normalizedKey] = (string) $found;
        }

        // 3. Fallback search by normalized name pattern
        $cleanSearch = str_replace(['cooca_', '_', '-'], ['%', ' ', ' '], $normalizedKey);
        $foundByName = DB::table('businesses')
            ->where('name', 'LIKE', "%{$cleanSearch}%")
            ->orWhere('slug', 'LIKE', "%{$cleanSearch}%")
            ->orWhere('slug', 'LIKE', "%{$alias}%")
            ->value('id');

        if ($foundByName !== null) {
            return self::$resolvedCache[$normalizedKey] = (string) $foundByName;
        }

        // 4. Industry category fallback
        $industryMap = [
            'cooca_mart' => 'retail',
            'mart' => 'retail',
            'retail' => 'retail',
            'cooca_bistro' => 'food_beverage',
            'bistro' => 'food_beverage',
            'fnb' => 'food_beverage',
            'cooca_mfg' => 'manufacturing',
            'mfg' => 'manufacturing',
            'manufacturing' => 'manufacturing',
            'cooca_autocare' => 'automotive',
            'autocare' => 'automotive',
            'workshop' => 'automotive',
        ];

        if (isset($industryMap[$normalizedKey])) {
            $category = $industryMap[$normalizedKey];
            $foundByCategory = DB::table('businesses')
                ->where('industry_category', $category)
                ->value('id');

            if ($foundByCategory !== null) {
                return self::$resolvedCache[$normalizedKey] = (string) $foundByCategory;
            }
        }

        // 5. Ultimate fallback: First active business in database
        $fallback = DB::table('businesses')->where('is_active', true)->value('id')
            ?? DB::table('businesses')->value('id');

        if ($fallback !== null) {
            return self::$resolvedCache[$normalizedKey] = (string) $fallback;
        }

        return null;
    }

    /**
     * Retrieve the Eloquent Business model instance by alias.
     */
    public static function getBusiness(string $alias): ?Business
    {
        $id = self::getBusinessId($alias);
        if ($id === null) {
            return null;
        }

        return Business::find($id);
    }

    /**
     * Create an isolated transient tenant for destructive or isolated mutation testing.
     *
     * @param array<string, mixed> $attributes
     */
    public static function createTransientTenant(array $attributes = []): Business
    {
        $uuid = (string) Str::uuid();
        $slug = 'test-transient-' . substr($uuid, 0, 8);

        $data = array_merge([
            'id' => $uuid,
            'name' => 'Transient E2E Tenant ' . substr($uuid, 0, 6),
            'slug' => $slug,
            'currency' => 'IDR',
            'currency_symbol' => 'Rp',
            'currency_precision' => 0,
            'is_active' => true,
            'template_code' => 'retail_reseller',
            'industry_category' => 'retail',
        ], $attributes);

        $business = Business::create($data);
        self::$transientTenantIds[] = $business->id;

        return $business;
    }

    /**
     * Clean up all created transient tenants and their associated records.
     */
    public static function cleanupTransientTenants(): void
    {
        // Scan for any transient or test-transient IDs in DB
        $transientSlugs = DB::table('businesses')
            ->where('slug', 'LIKE', 'transient-%')
            ->orWhere('slug', 'LIKE', 'test-transient-%')
            ->pluck('id')
            ->all();

        $idsToClean = array_unique(array_merge(self::$transientTenantIds, $transientSlugs));

        foreach ($idsToClean as $id) {
            try {
                // Delete child records where business_id is present
                $childTables = [
                    'journal_entry_lines',
                    'journal_entries',
                    'orders',
                    'order_items',
                    'products',
                    'locations',
                    'business_memberships',
                    'customers',
                    'suppliers',
                ];

                foreach ($childTables as $table) {
                    if (Schema::hasTable($table) && Schema::hasColumn($table, 'business_id')) {
                        DB::table($table)->where('business_id', $id)->delete();
                    }
                }

                DB::table('businesses')->where('id', $id)->delete();
            } catch (\Throwable) {
                // Non-destructive fallback: ignore cleanup failure
            }
        }

        self::$transientTenantIds = [];
    }

    /**
     * Reset cached business IDs.
     */
    public static function resetCache(): void
    {
        self::$resolvedCache = [];
    }
}
