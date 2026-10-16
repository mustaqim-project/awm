<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\BusinessSubscription;
use App\Models\Location;
use App\Models\Role;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TemplateRoutesTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;
    private Location $location;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RbacSeeder::class);

        $this->owner = User::create([
            'name' => 'Template Tester',
            'email' => 'template-tester@cooca.test',
            'password' => 'password123',
        ]);

        $this->business = Business::create([
            'name' => 'Test Business Cooca',
            'currency' => 'IDR',
        ]);

        $ownerRole = Role::where('slug', 'owner')->firstOrFail();

        $membership = BusinessMembership::create([
            'business_id' => $this->business->id,
            'user_id' => $this->owner->id,
            'role' => 'owner',
            'role_id' => $ownerRole->id,
            'status' => 'active',
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business, $membership);

        BusinessSubscription::updateOrCreate(
            ['business_id' => $this->business->id],
            [
                'plan_code' => BusinessSubscription::PLAN_PREMIUM_ANNUAL,
                'status' => BusinessSubscription::STATUS_ACTIVE,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addYear(),
            ]
        );

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Toko Cabang Utama',
            'code' => 'TKO-01',
            'is_active' => true,
        ]);

        $unit = \App\Models\Unit::create([
            'business_id' => $this->business->id,
            'name' => 'pcs',
            'code' => 'PCS',
            'category' => \App\Models\Unit::CATEGORY_QUANTITY,
        ]);

        \App\Models\Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $unit->id,
            'name' => 'Produk Tes Cooca',
            'code' => 'PRD-001',
            'slug' => 'produk-tes-cooca',
            'is_active' => true,
        ]);

        \App\Models\Material::create([
            'business_id' => $this->business->id,
            'unit_id' => $unit->id,
            'name' => 'Bahan Baku Tes',
            'code' => 'MAT-001',
            'slug' => 'bahan-baku-tes',
        ]);
    }

    public function test_inventory_template_download(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession(['current_business_id' => $this->business->id])
            ->get('/import/inventory/template?format=xlsx');

        $response->assertStatus(200);
        $this->assertEquals('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('Content-Type'));
    }

    public function test_opname_export_blank_with_location_id(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession(['current_business_id' => $this->business->id])
            ->get('/inventory/opnames/export-blank?location_id=' . $this->location->id);

        $response->assertStatus(200);
    }

    public function test_opname_export_blank_without_location_id(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession(['current_business_id' => $this->business->id])
            ->get('/inventory/opnames/export-blank');

        $response->assertStatus(200);
    }

    public function test_employee_template_direct_and_hrm_prefix(): void
    {
        // Root alias test
        $res1 = $this->actingAs($this->owner)
            ->withSession(['current_business_id' => $this->business->id])
            ->get('/employees/template');
        $res1->assertStatus(200);

        // HRM prefix test
        $res2 = $this->actingAs($this->owner)
            ->withSession(['current_business_id' => $this->business->id])
            ->get('/hrm/employees/template');
        $res2->assertStatus(200);
    }

    public function test_attendance_template_direct_and_hrm_prefix(): void
    {
        // Root alias test
        $res1 = $this->actingAs($this->owner)
            ->withSession(['current_business_id' => $this->business->id])
            ->get('/attendance/template');
        $res1->assertStatus(200);

        // HRM prefix test
        $res2 = $this->actingAs($this->owner)
            ->withSession(['current_business_id' => $this->business->id])
            ->get('/hrm/attendance/template');
        $res2->assertStatus(200);
    }

    public function test_inventory_template_prefilled_with_products_and_materials(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession(['current_business_id' => $this->business->id])
            ->get('/import/inventory/template?format=xlsx');

        $response->assertStatus(200);

        $content = $response->streamedContent();
        $tempPath = tempnam(sys_get_temp_dir(), 'test_prefilled_') . '.xlsx';
        file_put_contents($tempPath, $content);

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tempPath);
        $sheet = $spreadsheet->getActiveSheet();

        // Row 1: Headers
        $this->assertEquals('Nama Item / Produk / Bahan *', $sheet->getCell('A1')->getValue());

        // Row 2: Product 'Produk Tes Cooca'
        $this->assertEquals('Produk Tes Cooca', $sheet->getCell('A2')->getValue());
        $this->assertEquals('PRD-001', $sheet->getCell('B2')->getValue());
        $this->assertEquals('Toko Cabang Utama', $sheet->getCell('C2')->getValue());
        $this->assertEquals('pcs', $sheet->getCell('E2')->getValue());

        // Row 3: Material 'Bahan Baku Tes'
        $this->assertEquals('Bahan Baku Tes', $sheet->getCell('A3')->getValue());
        $this->assertEquals('MAT-001', $sheet->getCell('B3')->getValue());
        $this->assertEquals('Toko Cabang Utama', $sheet->getCell('C3')->getValue());
        $this->assertEquals('pcs', $sheet->getCell('E3')->getValue());

        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
    }
}
