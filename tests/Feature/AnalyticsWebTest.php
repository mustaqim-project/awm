<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\CommerceOrder;
use App\Models\Location;
use App\Models\Permission;
use App\Models\PosOrder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class AnalyticsWebTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private Location $outlet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->business = Business::create([
            'name' => 'Warkop Bento Nusantara',
            'business_scale' => 'umkm',
        ]);

        $this->owner = User::create([
            'name' => 'Owner Bento',
            'email' => 'owner@bento.test',
            'password' => 'password123',
            'email_verified_at' => now(),
            'phone' => '081234567890',
        ]);

        $ownerRole = Role::firstOrCreate(
            ['slug' => 'owner', 'business_id' => null],
            ['name' => 'Owner', 'description' => 'Owner with full access']
        );

        $perm = Permission::firstOrCreate(
            ['slug' => 'reports.view'],
            ['name' => 'View Reports', 'description' => 'View business reports and analytics']
        );
        $ownerRole->permissions()->syncWithoutDetaching([$perm->id]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole->id,
        ]);
        $this->owner->update(['active_business_id' => $this->business->id]);

        $this->outlet = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Utama',
            'type' => 'outlet',
            'is_active' => true,
        ]);
    }

    public function test_analytics_page_renders_successfully(): void
    {
        PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->outlet->id,
            'user_id' => $this->owner->id,
            'order_number' => 'POS-2026-001',
            'order_date' => now()->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'total_amount' => 150000,
            'paid_amount' => 150000,
            'total_hpp_cost' => 80000,
            'total_gross_profit' => 70000,
            'created_by' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('analytics.index'));

        $response->assertOk();
        $response->assertSee('Analitik Bisnis &amp; Tren Pertumbuhan', false);
        $response->assertSee(__('analytics.total_revenue'), false);
        $response->assertSee(__('analytics.gross_profit'), false);
        $response->assertSee(__('analytics.payment_title'), false);
    }

    public function test_analytics_json_api_returns_structured_metrics(): void
    {
        PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->outlet->id,
            'user_id' => $this->owner->id,
            'order_number' => 'POS-2026-002',
            'order_date' => now()->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'total_amount' => 500000,
            'paid_amount' => 500000,
            'total_hpp_cost' => 250000,
            'total_gross_profit' => 250000,
            'created_by' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson(route('analytics.index', ['period' => 'today']));

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.period', 'today');
        $this->assertEquals(500000, $response->json('data.current.revenue'));
        $this->assertEquals(250000, $response->json('data.current.gross_profit'));
    }

    public function test_analytics_handles_custom_date_range(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('analytics.index', [
                'period' => 'custom',
                'from' => '2026-01-01',
                'to' => '2026-01-31',
            ]));

        $response->assertOk();
        $response->assertSee('2026-01-01', false);
        $response->assertSee('2026-01-31', false);
    }

    public function test_unauthorized_user_without_reports_view_permission_is_forbidden(): void
    {
        $cashier = User::create([
            'name' => 'Cashier Only',
            'email' => 'cashier@bento.test',
            'password' => 'password123',
            'email_verified_at' => now(),
            'phone' => '081234567891',
        ]);

        $cashierRole = Role::firstOrCreate(
            ['slug' => 'cashier', 'business_id' => null],
            ['name' => 'Cashier', 'description' => 'Cashier role without reports']
        );

        $this->business->users()->attach($cashier->id, [
            'id' => (string) Str::uuid(),
            'role' => 'cashier',
            'role_id' => $cashierRole->id,
        ]);
        $cashier->update(['active_business_id' => $this->business->id]);

        // Web request redirects unauthorized staff to self-service portal
        $responseWeb = $this->actingAs($cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('analytics.index'));

        $responseWeb->assertRedirect(route('portal'));
        $responseWeb->assertSessionHas('error');

        // JSON / AJAX request returns 403 Forbidden
        $responseJson = $this->actingAs($cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson(route('analytics.index'));

        $responseJson->assertForbidden();
        $responseJson->assertJsonPath('error_code', 'FORBIDDEN_PERMISSION');
    }

    public function test_multitenant_isolation_prevents_viewing_other_business_data(): void
    {
        $otherBusiness = Business::create([
            'name' => 'Competitor Business Corp',
            'business_scale' => 'enterprise',
        ]);

        $otherLocation = Location::create([
            'business_id' => $otherBusiness->id,
            'name' => 'Competitor Outlet',
            'type' => 'outlet',
            'is_active' => true,
        ]);

        PosOrder::create([
            'business_id' => $otherBusiness->id,
            'location_id' => $otherLocation->id,
            'user_id' => $this->owner->id,
            'order_number' => 'POS-COMPETITOR-001',
            'order_date' => now()->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'total_amount' => 99000000,
            'paid_amount' => 99000000,
            'total_hpp_cost' => 45000000,
            'total_gross_profit' => 54000000,
            'created_by' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson(route('analytics.index', ['period' => 'today']));

        $response->assertOk();
        $this->assertEquals(0, $response->json('data.current.revenue'));
        $this->assertEquals(0, $response->json('data.current.gross_profit'));
    }

    public function test_analytics_today_period_populates_both_revenue_and_profit_trends(): void
    {
        PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->outlet->id,
            'user_id' => $this->owner->id,
            'order_number' => 'POS-TODAY-001',
            'order_date' => now()->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'total_amount' => 200000,
            'paid_amount' => 200000,
            'total_hpp_cost' => 100000,
            'total_gross_profit' => 100000,
            'created_by' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson(route('analytics.index', ['period' => 'today']));

        $response->assertOk();
        $labels = $response->json('data.trend.labels');
        $revenue = $response->json('data.trend.revenue');
        $profit = $response->json('data.trend.profit');

        // Harus 17 titik data dari 07:00 hingga 23:00
        $this->assertCount(17, $labels);
        $this->assertCount(17, $revenue);
        $this->assertCount(17, $profit);
        $this->assertEquals('07:00', $labels[0]);
        $this->assertEquals('23:00', $labels[16]);
    }

    public function test_analytics_handles_malformed_and_inverted_custom_dates_gracefully(): void
    {
        // 1. Malformed date input should not throw 500 error
        $responseMalformed = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson(route('analytics.index', [
                'period' => 'custom',
                'from' => 'invalid-garbage-date',
                'to' => 'malformed-input',
            ]));

        $responseMalformed->assertOk();
        $this->assertNotNull($responseMalformed->json('data.from'));

        // 2. Inverted dates (from > to) should auto-swap without error
        $responseInverted = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson(route('analytics.index', [
                'period' => 'custom',
                'from' => '2026-12-31',
                'to' => '2026-01-01',
            ]));

        $responseInverted->assertOk();
        $this->assertEquals('2026-01-01', $responseInverted->json('data.from'));
        $this->assertEquals('2026-12-31', $responseInverted->json('data.to'));
    }

    public function test_location_filter_scopes_pos_and_commerce_orders(): void
    {
        $secondOutlet = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Cabang Kedua',
            'type' => 'outlet',
            'is_active' => true,
        ]);

        // POS Order in outlet 1
        PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->outlet->id,
            'user_id' => $this->owner->id,
            'order_number' => 'POS-LOC1',
            'order_date' => now()->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'total_amount' => 100000,
            'paid_amount' => 100000,
            'total_hpp_cost' => 50000,
            'total_gross_profit' => 50000,
            'created_by' => $this->owner->id,
        ]);

        // Commerce Order in outlet 2
        CommerceOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $secondOutlet->id,
            'order_number' => 'COM-LOC2',
            'tracking_token' => 'TRK-LOC2-' . Str::random(8),
            'customer_name' => 'Customer B',
            'customer_phone' => '08123456789',
            'status' => CommerceOrder::STATUS_COMPLETED,
            'payment_status' => CommerceOrder::PAYMENT_PAID,
            'total_amount' => 250000,
        ]);

        // Query filtered by outlet 1
        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson(route('analytics.index', [
                'period' => 'today',
                'location_id' => $this->outlet->id,
            ]));

        $response->assertOk();
        // Omzet outlet 1 harus 100.000 (tidak tercampur 250.000 dari outlet 2)
        $this->assertEquals(100000, $response->json('data.current.revenue'));
        $this->assertEquals(0, $response->json('data.current.online_revenue'));
    }

    public function test_foreign_location_id_falls_back_safely(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson(route('analytics.index', [
                'period' => 'today',
                'location_id' => (string) Str::uuid(), // Random foreign UUID
            ]));

        $response->assertOk();
        $this->assertNull($response->json('data.selected_location_id'));
    }
}
