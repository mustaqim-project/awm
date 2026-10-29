<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Template\FeatureRegistry;
use App\Domain\Template\ModuleRegistry;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\Role;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\BusinessTemplateSeeder;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class SettingsIndustryAdaptiveModulesTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        Context::flush();
        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);
        $this->seed(RbacSeeder::class);
        $this->seed(BusinessTemplateSeeder::class);

        $this->owner = User::create([
            'name' => 'Owner Bisnis',
            'email' => 'owner.adaptive@cooca.test',
            'phone' => '6281122334455',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);
    }

    private function createTenantWithIndustry(?string $templateCode, string $name = 'Bisnis Pengujian'): Business
    {
        $business = Business::create([
            'name' => $name,
            'currency' => 'IDR',
            'template_code' => $templateCode,
            'industry_category' => $templateCode ? explode('_', $templateCode)[0] : null,
        ]);

        $ownerRole = Role::where('slug', 'owner')->first();
        $business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole?->id,
        ]);

        $this->owner->update(['active_business_id' => $business->id]);

        $membership = BusinessMembership::where('business_id', $business->id)
            ->where('user_id', $this->owner->id)
            ->first();
        Context::setBusiness($business, $membership);

        return $business;
    }

    public function test_fnb_industry_displays_only_relevant_modules_and_features(): void
    {
        $business = $this->createTenantWithIndustry('fnb_resto', 'Restoran Padang Enak');

        $response = $this->actingAs($this->owner, 'web')
            ->withSession([
                'active_business_id' => $business->id,
                'auth_wa_otp_verified_user_id' => $this->owner->id,
            ])
            ->get(route('settings.index', ['tab' => 'modules']));

        $response->assertOk();
        $response->assertViewHas('isFreeIndustry', false);
        $response->assertViewHas('relevantModuleKeys', function (array $keys) {
            return in_array(ModuleRegistry::MODULE_POS_DINEIN, $keys, true)
                && in_array(ModuleRegistry::MODULE_RECIPE_BOM, $keys, true)
                && ! in_array(ModuleRegistry::MODULE_SERVICE_WORKSHOP, $keys, true)
                && ! in_array(ModuleRegistry::MODULE_LABOR_MACHINES, $keys, true);
        });

        $response->assertViewHas('relevantFeatureKeys', function (array $keys) {
            return in_array(FeatureRegistry::POS_KITCHEN_ROUTING, $keys, true)
                && in_array(FeatureRegistry::POS_FOOD_WASTE_LOG, $keys, true)
                && ! in_array(FeatureRegistry::VERTICAL_WORKSHOP_HISTORY, $keys, true)
                && ! in_array(FeatureRegistry::VERTICAL_LAUNDRY_TRACKING, $keys, true)
                && ! in_array(FeatureRegistry::VERTICAL_PHARMACY_PRESCRIPT, $keys, true);
        });

        $response->assertSee('Zero Feature Leakage');
        $response->assertSee('Sesuai Industri');
        $response->assertSee('Tampilkan Semua (Bebas)');
    }

    public function test_workshop_industry_displays_workshop_modules_and_hides_fnb(): void
    {
        $business = $this->createTenantWithIndustry('service_workshop', 'Bengkel Auto Sejahtera');

        $response = $this->actingAs($this->owner, 'web')
            ->withSession([
                'active_business_id' => $business->id,
                'auth_wa_otp_verified_user_id' => $this->owner->id,
            ])
            ->get(route('settings.index', ['tab' => 'modules']));

        $response->assertOk();
        $response->assertViewHas('isFreeIndustry', false);
        $response->assertViewHas('relevantModuleKeys', function (array $keys) {
            return in_array(ModuleRegistry::MODULE_SERVICE_WORKSHOP, $keys, true)
                && ! in_array(ModuleRegistry::MODULE_POS_DINEIN, $keys, true)
                && ! in_array(ModuleRegistry::MODULE_RECIPE_BOM, $keys, true);
        });

        $response->assertViewHas('relevantFeatureKeys', function (array $keys) {
            return in_array(FeatureRegistry::VERTICAL_WORKSHOP_HISTORY, $keys, true)
                && in_array(FeatureRegistry::VERTICAL_WORKSHOP_REMINDER, $keys, true)
                && ! in_array(FeatureRegistry::POS_KITCHEN_ROUTING, $keys, true)
                && ! in_array(FeatureRegistry::VERTICAL_LAUNDRY_TRACKING, $keys, true);
        });
    }

    public function test_free_or_no_industry_displays_all_modules_and_all_features(): void
    {
        $business = $this->createTenantWithIndustry(null, 'Toko Usaha Bebas');

        $response = $this->actingAs($this->owner, 'web')
            ->withSession([
                'active_business_id' => $business->id,
                'auth_wa_otp_verified_user_id' => $this->owner->id,
            ])
            ->get(route('settings.index', ['tab' => 'modules']));

        $response->assertOk();
        $response->assertViewHas('isFreeIndustry', true);
        $response->assertViewHas('relevantModuleKeys', function (array $keys) {
            // Must contain ALL 17 modules
            return count($keys) === count(ModuleRegistry::definitions())
                && in_array(ModuleRegistry::MODULE_POS_DINEIN, $keys, true)
                && in_array(ModuleRegistry::MODULE_SERVICE_WORKSHOP, $keys, true)
                && in_array(ModuleRegistry::MODULE_RECIPE_BOM, $keys, true)
                && in_array(ModuleRegistry::MODULE_ACCOUNTING_CORPORATE, $keys, true);
        });

        $response->assertViewHas('relevantFeatureKeys', function (array $keys) {
            // Must contain ALL 24 parameters
            return count($keys) === count(FeatureRegistry::allKeys());
        });

        $response->assertSee('Mode Bebas');
    }
}

