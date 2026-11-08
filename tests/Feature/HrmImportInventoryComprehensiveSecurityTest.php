<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\BusinessSubscription;
use App\Models\Location;
use App\Models\MaterialCategory;
use App\Models\Payroll;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class HrmImportInventoryComprehensiveSecurityTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private User $unauthorizedUser;
    private User $materialsManager;
    private User $productsManager;
    private User $inventoryManager;
    private User $importManager;
    private User $hrmViewer;
    private User $usersViewer;
    private User $payrollViewer;
    private Payroll $samplePayroll;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RbacSeeder::class);

        $this->business = Business::create([
            'name' => 'PT Nusantara Security Test',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'business_scale' => Business::SCALE_CORPORATE,
            'disabled_modules' => [],
        ]);

        // Activate Core subscription so import feature is unlocked
        BusinessSubscription::updateOrCreate(
            ['business_id' => $this->business->id],
            [
                'plan_code' => BusinessSubscription::PLAN_CORE_MONTHLY,
                'status' => BusinessSubscription::STATUS_ACTIVE,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonth(),
            ]
        );

        // Seed master units and primary location for import execution
        Unit::firstOrCreate(['code' => 'pcs'], ['name' => 'Pcs', 'category' => 'quantity', 'is_base' => true]);
        Unit::firstOrCreate(['code' => 'cup'], ['name' => 'Cup', 'category' => 'quantity', 'is_base' => false]);
        Unit::firstOrCreate(['code' => 'kg'], ['name' => 'Kilogram', 'category' => 'weight', 'is_base' => true]);
        Unit::firstOrCreate(['code' => 'gram'], ['name' => 'Gram', 'category' => 'weight', 'is_base' => false]);

        MaterialCategory::firstOrCreate(
            ['business_id' => $this->business->id, 'name' => 'Bahan Baku Utama'],
            ['slug' => 'bahan-baku-utama']
        );

        Location::create([
            'business_id' => $this->business->id,
            'name' => 'Gudang Utama',
            'slug' => 'gudang-utama',
            'code' => 'GDG-01',
            'type' => 'warehouse',
            'is_active' => true,
        ]);

        // Owner user
        $this->owner = User::create([
            'name' => 'Security Owner',
            'email' => 'owner.security@cooca.test',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);
        $this->owner->update(['active_business_id' => $this->business->id]);

        // Cashier user without any manage, import, or HRM permissions
        $this->unauthorizedUser = User::create([
            'name' => 'Unauthorized Cashier',
            'email' => 'cashier.sec@cooca.test',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->business->users()->attach($this->unauthorizedUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'cashier',
            'is_active' => true,
        ]);
        $this->unauthorizedUser->update(['active_business_id' => $this->business->id]);

        // Granular permission users
        $this->materialsManager = $this->createUserWithPermissions(['materials.create'], 'Materials Manager');
        $this->productsManager = $this->createUserWithPermissions(['products.manage'], 'Products Manager');
        $this->inventoryManager = $this->createUserWithPermissions(['inventory.manage'], 'Inventory Manager');
        $this->importManager = $this->createUserWithPermissions(['import.manage'], 'Import Manager');

        $this->hrmViewer = $this->createUserWithPermissions(['hrm.view'], 'HRM Viewer');
        $this->usersViewer = $this->createUserWithPermissions(['users.view'], 'Users Viewer');
        $this->payrollViewer = $this->createUserWithPermissions(['hrm.payroll.view'], 'Payroll Viewer');

        // Sample Payroll record
        $this->samplePayroll = Payroll::create([
            'business_id' => $this->business->id,
            'period_month' => 11,
            'period_year' => 2026,
            'title' => 'Payroll Audit November 2026',
            'status' => Payroll::STATUS_DRAFT,
            'total_gross_pay' => 10000000,
            'total_take_home_pay' => 9500000,
            'total_employees_count' => 1,
        ]);
    }

    private function createUserWithPermissions(array $permissionSlugs, string $name): User
    {
        $user = User::create([
            'name' => $name,
            'email' => Str::slug($name) . '@cooca.test',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $role = Role::create([
            'name' => $name . ' Role',
            'slug' => 'role-' . Str::slug($name) . '-' . Str::random(5),
            'business_id' => $this->business->id,
        ]);

        $permissionIds = Permission::whereIn('slug', $permissionSlugs)->pluck('id');
        $role->permissions()->attach($permissionIds);

        $this->business->users()->attach($user->id, [
            'id' => (string) Str::uuid(),
            'role' => $role->slug,
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        $user->update(['active_business_id' => $this->business->id]);

        return $user;
    }

    public function test_unauthorized_user_forbidden_from_import_execute_endpoints(): void
    {
        $payload = [
            'rows' => json_encode([
                ['row_number' => 2, 'status' => 'valid', 'name' => 'Bahan X', 'unit' => 'kg', 'price' => 1000],
            ]),
            'duplicate_strategy' => 'skip',
        ];

        // 1. Materials Execute -> 403 Forbidden
        $responseMaterials = $this->actingAs($this->unauthorizedUser)
            ->postJson(route('import.materials.execute'), $payload);
        $responseMaterials->assertStatus(403)
            ->assertJsonPath('error_code', 'FORBIDDEN_PERMISSION');

        // 2. Products Execute -> 403 Forbidden
        $responseProducts = $this->actingAs($this->unauthorizedUser)
            ->postJson(route('import.products.execute'), $payload);
        $responseProducts->assertStatus(403)
            ->assertJsonPath('error_code', 'FORBIDDEN_PERMISSION');

        // 3. Recipes Execute -> 403 Forbidden
        $responseRecipes = $this->actingAs($this->unauthorizedUser)
            ->postJson(route('import.recipes.execute'), $payload);
        $responseRecipes->assertStatus(403)
            ->assertJsonPath('error_code', 'FORBIDDEN_PERMISSION');

        // 4. Inventory Execute -> 403 Forbidden
        $responseInventory = $this->actingAs($this->unauthorizedUser)
            ->postJson(route('import.inventory.execute'), $payload);
        $responseInventory->assertStatus(403)
            ->assertJsonPath('error_code', 'FORBIDDEN_PERMISSION');
    }

    public function test_users_with_domain_manage_permissions_can_execute_imports(): void
    {
        // 1. Materials Manager can execute material import (not 403)
        $matPayload = [
            'rows' => json_encode([
                [
                    'row_number' => 2,
                    'status' => 'valid',
                    'name' => 'Bahan Baku Security Test',
                    'code' => 'MAT-SEC-01',
                    'category' => 'Bahan Baku Utama',
                    'unit' => 'kg',
                    'standard_cost' => 25000,
                ],
            ]),
            'duplicate_strategy' => 'skip',
        ];
        $responseMat = $this->actingAs($this->materialsManager)
            ->postJson(route('import.materials.execute'), $matPayload);
        $this->assertNotEquals(403, $responseMat->status());
        $this->assertTrue(in_array($responseMat->status(), [200, 302], true));

        // 2. Products Manager can execute product import (not 403)
        $prodPayload = [
            'rows' => json_encode([
                [
                    'row_number' => 2,
                    'status' => 'valid',
                    'name' => 'Produk Jadi Security Test',
                    'sku' => 'PRD-SEC-01',
                    'category' => 'Minuman',
                    'unit' => 'cup',
                    'selling_price' => 18000,
                ],
            ]),
            'duplicate_strategy' => 'skip',
        ];
        $responseProd = $this->actingAs($this->productsManager)
            ->postJson(route('import.products.execute'), $prodPayload);
        $this->assertNotEquals(403, $responseProd->status());
        $this->assertTrue(in_array($responseProd->status(), [200, 302], true));

        // 3. Products Manager can execute recipe import (not 403)
        $recipePayload = [
            'rows' => json_encode([
                [
                    'row_number' => 2,
                    'status' => 'valid',
                    'product_name' => 'Produk Jadi Security Test',
                    'material_name' => 'Bahan Baku Security Test',
                    'quantity' => 10,
                    'unit' => 'gram',
                ],
            ]),
            'duplicate_strategy' => 'skip',
        ];
        $responseRecipe = $this->actingAs($this->productsManager)
            ->postJson(route('import.recipes.execute'), $recipePayload);
        $this->assertNotEquals(403, $responseRecipe->status());
        $this->assertTrue(in_array($responseRecipe->status(), [200, 302], true));

        // 4. Inventory Manager can execute inventory import (not 403)
        $invPayload = [
            'rows' => json_encode([
                [
                    'row_number' => 2,
                    'status' => 'valid',
                    'item_name' => 'Produk Jadi Security Test',
                    'sku' => 'PRD-SEC-01',
                    'quantity' => 50,
                    'unit' => 'cup',
                ],
            ]),
            'duplicate_strategy' => 'skip',
        ];
        $responseInv = $this->actingAs($this->inventoryManager)
            ->postJson(route('import.inventory.execute'), $invPayload);
        $this->assertNotEquals(403, $responseInv->status());
        $this->assertTrue(in_array($responseInv->status(), [200, 302], true));
    }

    public function test_user_with_import_manage_permission_can_execute_all_imports(): void
    {
        $payload = [
            'rows' => json_encode([
                [
                    'row_number' => 2,
                    'status' => 'valid',
                    'name' => 'Item Universal Test',
                    'sku' => 'ITEM-UNI-01',
                    'category' => 'Bahan Baku Utama',
                    'unit' => 'kg',
                    'standard_cost' => 15000,
                ],
            ]),
            'duplicate_strategy' => 'skip',
        ];

        // Global import.manage allows all 4 import executions
        $resMat = $this->actingAs($this->importManager)->postJson(route('import.materials.execute'), $payload);
        $this->assertNotEquals(403, $resMat->status());

        $resProd = $this->actingAs($this->importManager)->postJson(route('import.products.execute'), $payload);
        $this->assertNotEquals(403, $resProd->status());

        $resRecipe = $this->actingAs($this->importManager)->postJson(route('import.recipes.execute'), $payload);
        $this->assertNotEquals(403, $resRecipe->status());

        $resInv = $this->actingAs($this->importManager)->postJson(route('import.inventory.execute'), $payload);
        $this->assertNotEquals(403, $resInv->status());
    }

    public function test_unauthorized_user_forbidden_from_hrm_api_endpoints(): void
    {
        // 1. GET /api/v1/hrm/hub requires hrm.view or users.view -> 403
        $hubResponse = $this->actingAs($this->unauthorizedUser)
            ->getJson('/api/v1/hrm/hub');
        $hubResponse->assertStatus(403)
            ->assertJsonPath('error_code', 'FORBIDDEN_PERMISSION');

        // 2. POST /api/v1/hrm/employees requires hrm.manage or users.manage -> 403
        $empResponse = $this->actingAs($this->unauthorizedUser)
            ->postJson('/api/v1/hrm/employees', [
                'name' => 'Malicious Staff Mutation',
                'email' => 'malicious@test.local',
            ]);
        $empResponse->assertStatus(403)
            ->assertJsonPath('error_code', 'FORBIDDEN_PERMISSION');

        // 3. POST /api/v1/hrm/loans requires hrm.payroll.manage, hrm.manage, or users.manage -> 403
        $loanResponse = $this->actingAs($this->unauthorizedUser)
            ->postJson('/api/v1/hrm/loans', [
                'amount' => 1000000,
                'reason' => 'Kasbon Liar',
            ]);
        $loanResponse->assertStatus(403)
            ->assertJsonPath('error_code', 'FORBIDDEN_PERMISSION');
    }

    public function test_authorized_user_can_access_hrm_api_hub(): void
    {
        // hrm.view grants access to HRM API hub
        $hubHrm = $this->actingAs($this->hrmViewer)->getJson('/api/v1/hrm/hub');
        $hubHrm->assertStatus(200)
            ->assertJsonPath('success', true);

        // users.view grants fallback access to HRM API hub
        $hubUsers = $this->actingAs($this->usersViewer)->getJson('/api/v1/hrm/hub');
        $hubUsers->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_user_with_hrm_view_or_users_view_can_access_hrm_index(): void
    {
        // 1. User with hrm.view can access /hrm index
        $resHrm = $this->actingAs($this->hrmViewer)->get(route('hrm.index'));
        $resHrm->assertStatus(200);

        // 2. User with users.view can access /hrm index (dual-permission backward compatibility)
        $resUsers = $this->actingAs($this->usersViewer)->get(route('hrm.index'));
        $resUsers->assertStatus(200);

        // 3. Unauthorized user is blocked and redirected to portal
        $resUnauthorized = $this->actingAs($this->unauthorizedUser)->get(route('hrm.index'));
        $resUnauthorized->assertRedirect(route('portal'));
    }

    public function test_user_with_payroll_view_or_users_view_can_access_payroll_details(): void
    {
        // 1. User with hrm.payroll.view can access payroll details
        $resPayroll = $this->actingAs($this->payrollViewer)
            ->get(route('hrm.payrolls.show', $this->samplePayroll));
        $resPayroll->assertStatus(200);

        // 2. User with users.view can access payroll details (dual-permission fallback)
        $resUsers = $this->actingAs($this->usersViewer)
            ->get(route('hrm.payrolls.show', $this->samplePayroll));
        $resUsers->assertStatus(200);

        // 3. Unauthorized user is blocked and redirected to portal
        $resUnauthorized = $this->actingAs($this->unauthorizedUser)
            ->get(route('hrm.payrolls.show', $this->samplePayroll));
        $resUnauthorized->assertRedirect(route('portal'));
    }

    public function test_docs_render_comprehensive_business_owner_guides_for_hrm_import_inventory(): void
    {
        // 1. Verify docs index contains all 3 module anchors and bento guides
        $docsResponse = $this->actingAs($this->owner)->get(route('docs.index'));
        $docsResponse->assertStatus(200);
        $docsResponse->assertSee('sec-hrm', false);
        $docsResponse->assertSee('sec-import', false);
        $docsResponse->assertSee('sec-inventory', false);
        $docsResponse->assertSee('POV Business Owner');

        // 2. Verify standalone partial rendering for sec_hrm
        $hrmHtml = view('app.docs.partials.sec_hrm')->render();
        $this->assertStringContainsString('id="sec-hrm"', $hrmHtml);
        $this->assertStringContainsString('Penerapan Modul HRM pada 20 Jenis Usaha', $hrmHtml);
        $this->assertStringContainsString('Restoran Dine-In', $hrmHtml);
        $this->assertStringContainsString('x-data="{ expandedFaq: null }"', $hrmHtml);

        // 3. Verify standalone partial rendering for sec_import
        $importHtml = view('app.docs.partials.sec_import')->render();
        $this->assertStringContainsString('id="sec-import"', $importHtml);
        $this->assertStringContainsString('Penerapan Import Massal pada 20 Jenis Usaha', $importHtml);
        $this->assertStringContainsString('INPUT_*', $importHtml);
        $this->assertStringContainsString('DATA_REFERENSI', $importHtml);
        $this->assertStringContainsString('PANDUAN_IMPORT', $importHtml);
        $this->assertStringContainsString('Smart Living Lookups', $importHtml);

        // 4. Verify standalone partial rendering for sec_inventory
        $invHtml = view('app.docs.partials.sec_inventory')->render();
        $this->assertStringContainsString('id="sec-inventory"', $invHtml);
        $this->assertStringContainsString('Penerapan Modul Inventori pada 20 Jenis Usaha', $invHtml);
        $this->assertStringContainsString('Kopi Susu Gula Aren', $invHtml);
        $this->assertStringContainsString('PIN Otorisasi', $invHtml);
    }
}
