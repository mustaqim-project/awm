<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Template\ModuleRegistry;
use App\Models\Business;
use App\Models\GlobalCustomer;
use App\Models\Location;
use App\Models\PosTable;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultUnitSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StorefrontIndustryAdaptiveReservationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);
        $this->seed(RbacSeeder::class);
    }

    protected function tearDown(): void
    {
        Context::flush();
        parent::tearDown();
    }

    private function createMerchantUser(string $email = 'merchant@example.com'): User
    {
        $user = User::create([
            'name' => 'Owner Merchant',
            'email' => $email,
            'password' => bcrypt('password123'),
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
    }

    public function test_business_without_industry_has_reservation_disabled_and_cannot_access_backoffice(): void
    {
        $user = $this->createMerchantUser('free_biz@example.com');

        $business = Business::create([
            'name' => 'Toko Serba Serbi Umum',
            'slug' => 'toko-serba-serbi-umum',
            'template_code' => null,
            'industry_category' => null,
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        $business->users()->attach($user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $user->update(['active_business_id' => $business->id]);

        $this->assertFalse($business->hasIndustry());
        $this->assertFalse($business->supportsReservation());
        $this->assertFalse($business->isModuleEnabled(ModuleRegistry::MODULE_RESERVATION));

        // Merchant attempts to access backoffice /storefront/reservations
        $response = $this->actingAs($user)
            ->get(route('storefront.reservations.index'));

        // Should be blocked and redirected by CheckModuleEnabled middleware
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');

        // Customer attempts to post reservation to public storefront
        $customer = GlobalCustomer::create([
            'google_id' => 'cust-free-biz-1',
            'name' => 'Budi Customer',
            'phone' => '081234567890',
            'email' => 'budi@example.com',
            'phone_verified_at' => now(),
        ]);

        $payload = [
            'customer_name' => 'Budi Customer',
            'customer_phone' => '081234567890',
            'reservation_date' => now()->addDays(2)->toDateString(),
            'time_slot' => '14:00 - 15:00',
            'guest_count' => 2,
        ];

        $pubResponse = $this->actingAs($customer, 'customer')
            ->postJson(route('public.storefront.reservation.submit', $business->slug), $payload);

        $pubResponse->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    public function test_business_with_unsupported_industry_has_reservation_disabled(): void
    {
        $user = $this->createMerchantUser('pharmacy@example.com');

        $pharmacyBiz = Business::create([
            'name' => 'Apotek Sehat Medika',
            'slug' => 'apotek-sehat-medika',
            'template_code' => 'retail_pharmacy',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        $pharmacyBiz->users()->attach($user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $user->update(['active_business_id' => $pharmacyBiz->id]);

        $this->assertTrue($pharmacyBiz->hasIndustry());
        $this->assertFalse($pharmacyBiz->supportsReservation());
        $this->assertFalse($pharmacyBiz->isModuleEnabled(ModuleRegistry::MODULE_RESERVATION));

        // Accessing backoffice should be rejected
        $response = $this->actingAs($user)
            ->get(route('storefront.reservations.index'));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_fnb_resto_business_has_reservation_with_dining_tables_and_assign_modal(): void
    {
        $user = $this->createMerchantUser('resto@example.com');

        $restoBiz = Business::create([
            'name' => 'Nusantara Bistro Resto',
            'slug' => 'nusantara-bistro-resto',
            'template_code' => 'fnb_resto',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        $restoBiz->users()->attach($user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $user->update(['active_business_id' => $restoBiz->id]);

        $loc = Location::create([
            'business_id' => $restoBiz->id,
            'name' => 'Resto Utama',
            'is_primary' => true,
        ]);

        PosTable::create([
            'business_id' => $restoBiz->id,
            'location_id' => $loc->id,
            'table_number' => 'T-01',
            'name' => 'Meja Jendela VIP',
            'capacity' => 4,
            'status' => PosTable::STATUS_AVAILABLE,
            'is_active' => true,
        ]);

        $this->assertTrue($restoBiz->supportsReservation());
        $this->assertTrue($restoBiz->isModuleEnabled(ModuleRegistry::MODULE_RESERVATION));

        $response = $this->actingAs($user)
            ->get(route('storefront.reservations.index'));

        $response->assertOk();
        $response->assertSee(route('pos.tables.index'));
        $response->assertSee(__('storefront.backoffice.reservations.btn_pos_tables'));
        $response->assertSee('name="pos_table_id"', false);
    }

    public function test_workshop_business_has_reservation_with_services_and_zero_table_leakage(): void
    {
        $user = $this->createMerchantUser('bengkel@example.com');

        $workshopBiz = Business::create([
            'name' => 'Prima Motor Garasi',
            'slug' => 'prima-motor-garasi',
            'template_code' => 'service_workshop',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        $workshopBiz->users()->attach($user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $user->update(['active_business_id' => $workshopBiz->id]);

        $unit = Unit::firstOrFail();

        Product::create([
            'business_id' => $workshopBiz->id,
            'name' => 'Paket Servis Ringan & Ganti Oli',
            'slug' => 'paket-servis-ringan',
            'type' => 'service',
            'output_unit_id' => $unit->id,
            'selling_price' => 150000,
            'is_active' => true,
        ]);

        $this->assertTrue($workshopBiz->supportsReservation());
        $this->assertTrue($workshopBiz->isModuleEnabled(ModuleRegistry::MODULE_RESERVATION));

        $response = $this->actingAs($user)
            ->get(route('storefront.reservations.index'));

        $response->assertOk();

        // Workshop specific UI present
        $response->assertSee(__('storefront.backoffice.reservations.pos_strip_workshop_title'));
        $response->assertSee(__('storefront.backoffice.reservations.kpi_services_workshop'));
        $response->assertSee(__('storefront.backoffice.reservations.btn_pos_services'));
        $response->assertSee(__('storefront.backoffice.reservations.btn_pos_workshop_terminal'));

        // ZERO FEATURE LEAKAGE: Dine-in table elements MUST NOT leak to workshop
        $response->assertDontSee(route('pos.tables.index'));
        $response->assertDontSee('name="pos_table_id"', false);
    }

    public function test_barbershop_business_has_reservation_with_treatment_menu_and_zero_table_leakage(): void
    {
        $user = $this->createMerchantUser('barber@example.com');

        $barberBiz = Business::create([
            'name' => 'Gatsby Gentleman Barbershop',
            'slug' => 'gatsby-gentleman-barber',
            'template_code' => 'service_barbershop',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        $barberBiz->users()->attach($user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $user->update(['active_business_id' => $barberBiz->id]);

        $this->assertTrue($barberBiz->supportsReservation());
        $this->assertTrue($barberBiz->isModuleEnabled(ModuleRegistry::MODULE_RESERVATION));

        $response = $this->actingAs($user)
            ->get(route('storefront.reservations.index'));

        $response->assertOk();

        // Barbershop specific UI present
        $response->assertSee(__('storefront.backoffice.reservations.pos_strip_barber_title'));
        $response->assertSee(__('storefront.backoffice.reservations.kpi_services_barber'));
        $response->assertSee(__('storefront.backoffice.reservations.btn_pos_treatment'));

        // ZERO FEATURE LEAKAGE: Dine-in tables must NOT exist
        $response->assertDontSee(route('pos.tables.index'));
        $response->assertDontSee('name="pos_table_id"', false);
    }
}
