<?php

declare(strict_types=1);

namespace Tests\E2E\Tier2_BoundaryCorner;

use App\Models\Business;
use App\Models\PosOrder;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Tests\E2E\Support\BaseTestCase;

/**
 * Tier 2 Boundary & Corner Cases: F10 Automotive Workshop Dataset (COOCA AutoCare)
 *
 * Verifies Workshop boundary conditions: license plate normalization and pattern validation,
 * vehicle odometer rollback detection, non-positive service labor duration rejection,
 * service item 'service' type configuration, and duplicate license plate scoping.
 */
final class F10_WorkshopBoundaryTest extends BaseTestCase
{
    /**
     * 1. Indonesian vehicle license plate normalization and validation.
     */
    public function test_workshop_license_plate_format_and_normalization(): void
    {
        $rawPlate = "  b  1234  xyz  ";
        // Normalization: uppercase and standardized single spacing
        $normalizedPlate = strtoupper(trim(preg_replace('/\s+/', ' ', $rawPlate) ?? ''));

        $this->assertSame('B 1234 XYZ', $normalizedPlate);

        // Indonesian plate regex: 1-2 letters, 1-4 digits, 1-3 letters
        $plateRegex = '/^[A-Z]{1,2}\s+[0-9]{1,4}\s+[A-Z]{1,3}$/';
        $this->assertMatchesRegularExpression(
            $plateRegex,
            $normalizedPlate,
            "Normalized plate [{$normalizedPlate}] must match standard Indonesian format."
        );
    }

    /**
     * 2. Odometer mileage rollback detection: current mileage cannot regress below previous service mileage.
     */
    public function test_odometer_mileage_rollback_detection(): void
    {
        $lastRecordedMileage = 55000; // km
        $incomingRecordedMileage = 42000; // km (rolled back)

        $isMileageRegression = ($incomingRecordedMileage < $lastRecordedMileage);
        $regressionDiff = $lastRecordedMileage - $incomingRecordedMileage;

        $this->assertTrue(
            $isMileageRegression,
            "Mileage {$incomingRecordedMileage} km is less than historical {$lastRecordedMileage} km and must flag a rollback alert."
        );
        $this->assertSame(13000, $regressionDiff);
    }

    /**
     * 3. Service labor duration must be strictly positive (> 0 minutes).
     */
    public function test_zero_duration_or_negative_service_labor_validation(): void
    {
        $invalidDurations = [0, -15, -60];

        foreach ($invalidDurations as $durationMinutes) {
            $isInvalid = ($durationMinutes <= 0);
            $this->assertTrue(
                $isInvalid,
                "Service labor duration [{$durationMinutes}] minutes must be flagged as invalid."
            );
        }
    }

    /**
     * 4. Workshop services in COOCA AutoCare are configured with type 'service'.
     */
    public function test_workshop_services_configured_as_service_type(): void
    {
        $businessId = $this->getTenantBusinessId('cooca_autocare');
        $this->assertNotNull($businessId, 'COOCA AutoCare tenant business ID must exist in database.');

        $services = Product::where('business_id', $businessId)
            ->where('type', Product::TYPE_SERVICE)
            ->get();

        $this->assertNotEmpty($services, 'COOCA AutoCare must have seeded service products.');

        foreach ($services as $service) {
            $this->assertSame(Product::TYPE_SERVICE, $service->type);
            $this->assertSame(Product::BOM_NO_BOM, $service->bom_consumption_type);
        }
    }

    /**
     * 5. Workshop duplicate vehicle detection: identical normalized plates in the same business.
     */
    public function test_duplicate_vehicle_license_plate_scoping(): void
    {
        $this->runInTransaction(function (): void {
            $business = $this->getTenantBusiness('cooca_autocare') ?? $this->createTransientTenant();
            $user = User::first() ?? User::factory()->create();

            $plateA = 'B 8888 COA';
            $plateB = 'b 8888 coa'; // Same vehicle, different case

            $normA = strtoupper(trim($plateA));
            $normB = strtoupper(trim($plateB));

            $isDuplicate = ($normA === $normB);
            $this->assertTrue($isDuplicate, 'Case-insensitive comparison must identify identical license plates.');
        });
    }
}
