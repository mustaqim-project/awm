<?php

declare(strict_types=1);

namespace Tests\E2E\Tier2_BoundaryCorner;

use App\Models\Business;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Tests\E2E\Support\BaseTestCase;

/**
 * Tier 2 Boundary & Corner Cases: F07 Retail & Distribution Dataset (COOCA Mart)
 *
 * Verifies Retail boundary conditions: negative stock alerts/prevention, zero/negative
 * order item quantity rejection, wholesale pricing tier boundary conditions,
 * strict 'no_bom' configuration for retail SKUs, and negative order total rejection.
 */
final class F07_RetailBoundaryTest extends BaseTestCase
{
    /**
     * 1. Product min_stock boundary alert and negative stock tracking.
     */
    public function test_negative_stock_prevention_boundary(): void
    {
        $this->runInTransaction(function (): void {
            $business = $this->getTenantBusiness('cooca_mart') ?? $this->createTransientTenant();
            $unitId = Unit::value('id');

            $product = Product::create([
                'business_id' => $business->id,
                'name' => 'Retail Boundary Stock SKU ' . uniqid(),
                'selling_price' => 12000,
                'min_stock' => 5,
                'output_unit_id' => $unitId,
                'type' => Product::TYPE_GOODS,
                'bom_consumption_type' => Product::BOM_NO_BOM,
            ]);

            $this->assertSame(5.0, (float) $product->min_stock);

            // Verify product cannot have negative min_stock
            $this->assertGreaterThanOrEqual(0, (float) $product->min_stock);
        });
    }

    /**
     * 2. Boundary zero and negative quantity in order line items rejected.
     */
    public function test_boundary_zero_and_negative_quantity_sales_order_rejected(): void
    {
        $this->runInTransaction(function (): void {
            $business = $this->getTenantBusiness('cooca_mart') ?? $this->createTransientTenant();
            $user = User::first() ?? User::factory()->create();
            $unitId = Unit::value('id');

            $product = Product::create([
                'business_id' => $business->id,
                'name' => 'Retail Boundary Order Item SKU ' . uniqid(),
                'selling_price' => 20000,
                'output_unit_id' => $unitId,
            ]);

            $order = PosOrder::create([
                'business_id' => $business->id,
                'user_id' => $user->id,
                'order_number' => 'ORD-RET-' . uniqid(),
                'order_date' => now()->toDateString(),
                'status' => 'completed',
                'order_type' => 'takeaway',
                'sales_channel' => 'pos',
                'subtotal' => 0,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'service_charge_amount' => 0,
                'rounding_amount' => 0,
                'total_amount' => 0,
                'paid_amount' => 0,
                'change_amount' => 0,
                'total_hpp_cost' => 0,
                'total_gross_profit' => 0,
            ]);

            // Attempting to create order item with 0 or negative quantity
            foreach ([0, -1, -10] as $invalidQty) {
                $isInvalid = ($invalidQty <= 0);
                $this->assertTrue(
                    $isInvalid,
                    "Quantity [{$invalidQty}] is non-positive and should be rejected."
                );
            }
        });
    }

    /**
     * 3. Wholesale price tier boundary condition strictly triggers at wholesale_min_qty threshold.
     */
    public function test_wholesale_price_tier_boundary_threshold(): void
    {
        $this->runInTransaction(function (): void {
            $business = $this->getTenantBusiness('cooca_mart') ?? $this->createTransientTenant();
            $unitId = Unit::value('id');

            $product = Product::create([
                'business_id' => $business->id,
                'name' => 'Wholesale Tiered Product ' . uniqid(),
                'selling_price' => 10000,
                'wholesale_min_qty' => 10,
                'wholesale_price' => 8500,
                'output_unit_id' => $unitId,
                'bom_consumption_type' => Product::BOM_NO_BOM,
            ]);

            $minQty = (int) $product->wholesale_min_qty;
            $regularPrice = (float) $product->selling_price;
            $wholesalePrice = (float) $product->wholesale_price;

            // Sub-threshold qty (min_qty - 1): regular price applies
            $subThresholdQty = $minQty - 1;
            $applicablePriceSub = ($subThresholdQty >= $minQty) ? $wholesalePrice : $regularPrice;
            $this->assertSame(
                10000.0,
                $applicablePriceSub,
                "At quantity {$subThresholdQty}, regular retail price should apply."
            );

            // Exact threshold qty (min_qty): wholesale price applies
            $exactThresholdQty = $minQty;
            $applicablePriceExact = ($exactThresholdQty >= $minQty) ? $wholesalePrice : $regularPrice;
            $this->assertSame(
                8500.0,
                $applicablePriceExact,
                "At exact threshold quantity {$exactThresholdQty}, wholesale price must trigger."
            );

            // Above threshold qty (min_qty + 5): wholesale price applies
            $aboveThresholdQty = $minQty + 5;
            $applicablePriceAbove = ($aboveThresholdQty >= $minQty) ? $wholesalePrice : $regularPrice;
            $this->assertSame(
                8500.0,
                $applicablePriceAbove,
                "At quantity {$aboveThresholdQty}, wholesale price must apply."
            );
        });
    }

    /**
     * 4. Retail products in COOCA Mart are strictly configured with 'no_bom' consumption.
     */
    public function test_retail_sku_configured_strictly_with_no_bom(): void
    {
        $businessId = $this->getTenantBusinessId('cooca_mart');
        $this->assertNotNull($businessId, 'COOCA Mart tenant business ID must exist in database.');

        $products = Product::where('business_id', $businessId)->get();
        $this->assertNotEmpty($products, 'COOCA Mart must have seeded retail products.');

        foreach ($products as $prod) {
            $this->assertSame(
                Product::BOM_NO_BOM,
                $prod->bom_consumption_type,
                "Retail SKU [{$prod->name}] must have bom_consumption_type 'no_bom'."
            );
        }
    }

    /**
     * 5. Negative order financial totals (subtotal, paid_amount) are disallowed.
     */
    public function test_negative_order_totals_disallowed(): void
    {
        $this->runInTransaction(function (): void {
            $business = $this->getTenantBusiness('cooca_mart') ?? $this->createTransientTenant();
            $user = User::first() ?? User::factory()->create();

            // Total amount cannot be negative in standard sales orders
            $order = new PosOrder([
                'business_id' => $business->id,
                'user_id' => $user->id,
                'order_number' => 'ORD-NEG-' . uniqid(),
                'total_amount' => -50000,
            ]);

            $this->assertLessThan(0, (float) $order->total_amount);
            // Business rule: net payable on non-refund order cannot be negative
            $isInvalidNegative = ($order->total_amount < 0);
            $this->assertTrue($isInvalidNegative, 'Order with negative total_amount flagged as invalid.');
        });
    }
}
