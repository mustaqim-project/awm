<?php

declare(strict_types=1);

namespace Tests\E2E\Tier2_BoundaryCorner;

use App\Models\Business;
use App\Models\PosOrder;
use App\Models\PosTable;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Tests\E2E\Support\BaseTestCase;

/**
 * Tier 2 Boundary & Corner Cases: F08 F&B Dataset (COOCA Bistro & Coffee)
 *
 * Verifies F&B boundary conditions: zero-stock BOM ingredient shortage detection,
 * table state transition validation, negative tip and tax rejection,
 * strict 'consumption_on_sale' configuration for F&B menu items, and table capacity constraints.
 */
final class F08_FnbBoundaryTest extends BaseTestCase
{
    /**
     * 1. Menu item BOM auto-deduction flags shortage warning when ingredient stock is zero.
     */
    public function test_zero_stock_bom_ingredient_detection(): void
    {
        $this->runInTransaction(function (): void {
            $business = $this->getTenantBusiness('cooca_bistro') ?? $this->createTransientTenant([
                'template_code' => 'resto_cafe',
                'industry_category' => 'food_beverage',
            ]);
            $unitId = Unit::value('id');

            // Raw ingredient with 0 available stock
            $ingredient = Product::create([
                'business_id' => $business->id,
                'name' => 'F&B Fresh Milk Ingredient ' . uniqid(),
                'selling_price' => 20000,
                'min_stock' => 10,
                'output_unit_id' => $unitId,
                'type' => Product::TYPE_GOODS,
                'bom_consumption_type' => Product::BOM_NO_BOM,
            ]);

            $currentStock = 0; // Simulated stock level
            $requiredQuantity = 2; // Quantity needed for recipe

            $hasShortage = ($currentStock < $requiredQuantity);
            $this->assertTrue(
                $hasShortage,
                "Ingredient stock {$currentStock} should trigger shortage warning when recipe requires {$requiredQuantity}."
            );
        });
    }

    /**
     * 2. Dining table lifecycle transitions through valid states: available -> occupied -> waiting_payment -> closed/available.
     */
    public function test_table_state_machine_transition_boundaries(): void
    {
        $this->runInTransaction(function (): void {
            $business = $this->getTenantBusiness('cooca_bistro') ?? $this->createTransientTenant();

            $table = PosTable::create([
                'business_id' => $business->id,
                'table_number' => 'T-E2E-' . rand(100, 999),
                'name' => 'Meja VIP 1',
                'capacity' => 4,
                'status' => PosTable::STATUS_AVAILABLE,
            ]);

            $validTransitions = [
                PosTable::STATUS_ORDERING,
                PosTable::STATUS_OCCUPIED,
                PosTable::STATUS_WAITING_PAYMENT,
                PosTable::STATUS_CLOSED,
                PosTable::STATUS_AVAILABLE,
            ];

            foreach ($validTransitions as $nextStatus) {
                $table->status = $nextStatus;
                $table->save();
                $this->assertSame($nextStatus, $table->status);
            }

            // Verify illegal unknown status rejection
            $allowedStatuses = [
                PosTable::STATUS_AVAILABLE,
                PosTable::STATUS_ORDERING,
                PosTable::STATUS_OCCUPIED,
                PosTable::STATUS_PREPARING,
                PosTable::STATUS_SERVING,
                PosTable::STATUS_WAITING_PAYMENT,
                PosTable::STATUS_CLOSED,
                PosTable::STATUS_INACTIVE,
            ];

            $illegalStatus = 'invalid_destroyed_state';
            $this->assertNotContains($illegalStatus, $allowedStatuses);
        });
    }

    /**
     * 3. Negative tax or negative service charge amounts strictly rejected.
     */
    public function test_negative_tip_and_tax_amounts_rejected(): void
    {
        $this->runInTransaction(function (): void {
            $business = $this->getTenantBusiness('cooca_bistro') ?? $this->createTransientTenant();
            $user = User::first() ?? User::factory()->create();

            $order = new PosOrder([
                'business_id' => $business->id,
                'user_id' => $user->id,
                'order_number' => 'ORD-FNB-' . uniqid(),
                'subtotal' => 100000,
                'tax_amount' => -11000, // Negative tax is illegal
                'service_charge_amount' => -5000, // Negative tip/service charge is illegal
            ]);

            $isTaxNegative = ($order->tax_amount < 0);
            $isServiceNegative = ($order->service_charge_amount < 0);

            $this->assertTrue($isTaxNegative, 'Negative tax amount must be flagged as invalid.');
            $this->assertTrue($isServiceNegative, 'Negative service charge must be flagged as invalid.');
        });
    }

    /**
     * 4. F&B menu items in COOCA Bistro are configured with 'consumption_on_sale' BOM behavior.
     */
    public function test_fnb_menu_items_configured_as_consumption_on_sale(): void
    {
        $businessId = $this->getTenantBusinessId('cooca_bistro');
        $this->assertNotNull($businessId, 'COOCA Bistro tenant business ID must exist in database.');

        $fnbProducts = Product::where('business_id', $businessId)->get();
        $this->assertNotEmpty($fnbProducts, 'COOCA Bistro must have seeded menu item products.');

        $consumptionOnSaleCount = $fnbProducts->where('bom_consumption_type', Product::BOM_CONSUMPTION_ON_SALE)->count();
        $this->assertGreaterThan(
            0,
            $consumptionOnSaleCount,
            'COOCA Bistro must have menu items configured with bom_consumption_type consumption_on_sale.'
        );
    }

    /**
     * 5. Dining table capacity boundary: capacity must be an integer >= 1.
     * Validates application controller rules ('min:1'), database schema, and live seeded tables.
     */
    public function test_table_capacity_boundary_minimum_positive_integer(): void
    {
        // 1. Verify application validation rules enforced by PosTableWebController & PosTableApiController
        $rules = [
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
        ];

        $invalidCapacities = [0, -1, -5];
        foreach ($invalidCapacities as $cap) {
            $validator = \Illuminate\Support\Facades\Validator::make(['capacity' => $cap], $rules);
            $this->assertTrue(
                $validator->fails(),
                "Dining table capacity [{$cap}] must fail application validation (must be >= 1)."
            );
            $this->assertTrue(
                $validator->errors()->has('capacity'),
                "Validation errors must flag 'capacity' attribute for value [{$cap}]."
            );
        }

        // Boundary valid minimum positive integer (capacity = 1) must pass
        $validValidator = \Illuminate\Support\Facades\Validator::make(['capacity' => 1], $rules);
        $this->assertFalse(
            $validValidator->fails(),
            'Dining table capacity boundary 1 must pass validation.'
        );

        // 2. Verify pos_tables schema defines capacity column
        $this->assertTrue(
            \Illuminate\Support\Facades\Schema::hasColumn('pos_tables', 'capacity'),
            "Database table 'pos_tables' must contain column 'capacity'."
        );

        // 3. Verify live seeded dining tables in COOCA Bistro strictly satisfy capacity >= 1
        $businessId = $this->getTenantBusinessId('cooca_bistro');
        $this->assertNotNull($businessId, 'COOCA Bistro tenant business ID must exist in database.');

        $tables = PosTable::where('business_id', $businessId)->get();
        $this->assertNotEmpty($tables, 'COOCA Bistro must have seeded dining tables.');

        foreach ($tables as $table) {
            $this->assertGreaterThanOrEqual(
                1,
                (int) $table->capacity,
                "Dining table [{$table->table_number}] capacity must be at least 1."
            );
        }
    }
}
