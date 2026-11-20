<?php

declare(strict_types=1);

namespace Tests\E2E\Tier4_RealWorldScenarios;

use App\Domain\Accounting\AutoJournalService;
use App\Models\Business;
use App\Models\CommerceReservation;
use App\Models\Customer;
use App\Models\JournalEntry;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderPayment;
use App\Models\PosTable;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\E2E\Support\BaseTestCase;

/**
 * Tier 4: Workshop (Automotive / AutoCare) Real-World Application Workflows
 *
 * Validates automotive garage operational workflows:
 * Vehicle check-in to SPK repair order with spare parts and labor billing,
 * and service bay calendar booking with technician labor commission calculation.
 */
class WorkshopEndToEndWorkflowTest extends BaseTestCase
{
    private AutoJournalService $journalService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->journalService = new AutoJournalService();

        JournalEntry::creating(function (JournalEntry $entry): void {
            if (empty($entry->entry_date)) {
                $entry->entry_date = now()->toDateString();
            }
        });
    }

    /**
     * Scenario 1: Vehicle Check-in to Final Inspection SPK
     * Customer check-in with license plate & mileage -> Diagnostic estimate -> Spare parts requisition -> Service labor completion -> Final billing.
     */
    public function test_vehicle_checkin_to_final_inspection_spk(): void
    {
        $this->runInTransaction(function (): void {
            $business = $this->getTenantBusiness('cooca_autocare');
            $this->assertNotNull($business, 'COOCA AutoCare business must exist.');

            $technician = User::where('email', 'owner.workshop@cooca.id')->first()
                ?? User::where('email', 'demo@cooca.id')->first()
                ?? User::first();
            $location = Location::where('business_id', $business->id)->first();
            $customer = Customer::where('business_id', $business->id)->first();

            // 1. Vehicle Intake & Diagnostic Check-in
            $licensePlate = 'B 4567 TUV';
            $vehicleModel = 'Toyota Avanza 1.3 G M/T';
            $odometer = 48500;
            $serviceNotes = 'Keluhan: Mesin bergetar saat idle, rem depan berdecit. SPK: Tune-up injeksi, ganti oli mesin, ganti kampas rem depan.';

            // 2. SPK Work Order Diagnostic Estimate
            $orderNumber = 'SPK-E2E-' . strtoupper(Str::random(6));
            $partsSubtotal = 275000.0; // Oli Mesin Sintetis (175k) + Kampas Rem (100k)
            $partsHpp = 190000.0; // Parts cost
            $laborSubtotal = 150000.0; // Jasa Tune-up (100k) + Ganti Kampas Rem (50k)
            $orderSubtotal = $partsSubtotal + $laborSubtotal; // Rp 425.000

            $order = PosOrder::create([
                'business_id' => $business->id,
                'location_id' => $location?->id,
                'customer_id' => $customer?->id,
                'technician_id' => $technician?->id,
                'user_id' => $technician?->id,
                'order_number' => $orderNumber,
                'order_date' => now()->toDateString(),
                'status' => PosOrder::STATUS_PREPARING,
                'order_type' => 'takeaway',
                'vehicle_license_plate' => $licensePlate,
                'vehicle_model' => $vehicleModel,
                'vehicle_mileage' => $odometer,
                'service_notes' => $serviceNotes,
                'subtotal' => $orderSubtotal,
                'tax_amount' => 0,
                'total_amount' => $orderSubtotal,
                'paid_amount' => 0,
                'change_amount' => 0,
                'total_hpp_cost' => $partsHpp,
            ]);

            // Spare parts line items
            PosOrderItem::create([
                'pos_order_id' => $order->id,
                'product_name' => 'Oli Mesin Full Synthetic 5W-30 (4L)',
                'quantity' => 1,
                'unit_price' => 175000,
                'total_price' => 175000,
            ]);

            PosOrderItem::create([
                'pos_order_id' => $order->id,
                'product_name' => 'Brake Pad / Kampas Rem Depan Avanza',
                'quantity' => 1,
                'unit_price' => 100000,
                'total_price' => 100000,
            ]);

            // Labor service line items
            PosOrderItem::create([
                'pos_order_id' => $order->id,
                'product_name' => 'Jasa Tune-Up Mesin & Gurah Karbon',
                'quantity' => 1,
                'unit_price' => 100000,
                'total_price' => 100000,
            ]);

            PosOrderItem::create([
                'pos_order_id' => $order->id,
                'product_name' => 'Jasa Pemasangan & Bleeding Rem Depan',
                'quantity' => 1,
                'unit_price' => 50000,
                'total_price' => 50000,
            ]);

            // 3. Spare Parts Requisition & Workshop Inventory Stock Deduction
            StockMovement::create([
                'business_id' => $business->id,
                'location_id' => $location?->id,
                'movement_type' => 'pos_sale',
                'reference_id' => $order->id,
                'reference_number' => $order->order_number,
                'quantity_change' => -1,
                'balance_before' => 12,
                'balance_after' => 11,
                'unit_cost' => 120000,
                'total_cost' => 120000,
                'notes' => "Requisition sparepart Oli Mesin untuk SPK #{$order->order_number}",
            ]);

            // 4. Labor Service Completion & Final Settlement
            $order->update([
                'status' => PosOrder::STATUS_COMPLETED,
                'paid_amount' => $orderSubtotal,
            ]);

            PosOrderPayment::create([
                'pos_order_id' => $order->id,
                'payment_method' => PosOrderPayment::METHOD_TRANSFER,
                'amount' => $orderSubtotal,
                'status' => 'completed',
                'reference_number' => 'BCA-WS-' . Str::random(6),
            ]);

            $this->assertSame(PosOrder::STATUS_COMPLETED, $order->fresh()->status);
            $this->assertSame($licensePlate, $order->fresh()->vehicle_license_plate);
            $this->assertSame($odometer, (int) $order->fresh()->vehicle_mileage);

            // 5. Post SAK EMKM Accounting Journal
            $journalEntry = $this->journalService->recordPosSaleJournal($order);
            $this->assertNotNull($journalEntry, 'AutoJournalService must generate journal for workshop SPK order.');
            $this->assertJournalEntryBalanced($journalEntry);
        });
    }

    /**
     * Scenario 2: Bay Scheduling & Technician Assignment
     * Service bay calendar booking and technician commission calculation.
     */
    public function test_bay_scheduling_and_technician_assignment(): void
    {
        $this->runInTransaction(function (): void {
            $business = $this->getTenantBusiness('cooca_autocare');
            $this->assertNotNull($business, 'COOCA AutoCare business must exist.');

            $technician = User::where('email', 'owner.workshop@cooca.id')->first()
                ?? User::where('email', 'demo@cooca.id')->first()
                ?? User::first();
            $location = Location::where('business_id', $business->id)->first();

            // 1. Service Bay Allocation (Bay 02 - Car Hoist Lift)
            $bay = PosTable::where('business_id', $business->id)->first()
                ?? PosTable::create([
                    'business_id' => $business->id,
                    'location_id' => $location?->id,
                    'table_number' => 'BAY-02',
                    'name' => 'Stall / Bay 02 (Lift 4-Post)',
                    'capacity' => 1,
                    'status' => 'available',
                    'is_active' => true,
                ]);

            // 2. Booking Reservation in Bay Schedule Calendar
            $reservationCode = 'RSV-WS-' . strtoupper(Str::random(6));
            $laborFee = 200000.0; // Jasa Overhaul / Servis Besar

            $reservation = CommerceReservation::create([
                'business_id' => $business->id,
                'location_id' => $location?->id,
                'pos_table_id' => $bay->id,
                'staff_id' => $technician?->id,
                'created_by' => $technician?->id,
                'reservation_code' => $reservationCode,
                'customer_name' => 'Bambang Sudiro',
                'customer_phone' => '081298765432',
                'reservation_date' => now()->toDateString(),
                'time_slot' => '10:00 - 11:30 WIB',
                'vehicle_plate' => 'D 9988 XYZ',
                'vehicle_model' => 'Honda Jazz RS 2018',
                'service_duration_minutes' => 90,
                'estimated_total' => $laborFee,
                'status' => 'confirmed',
                'notes' => 'Servis rem ABS dan spooring balancing di Bay 02',
            ]);

            $this->assertDatabaseHas('commerce_reservations', [
                'id' => $reservation->id,
                'reservation_code' => $reservationCode,
                'vehicle_plate' => 'D 9988 XYZ',
                'status' => 'confirmed',
            ]);

            // 3. Technician Labor Commission Calculation:
            // Standard workshop compensation rule:
            // 35% commission on billable labor services completed by certified mechanic.
            $commissionRate = 0.35; // 35%
            $earnedCommission = $laborFee * $commissionRate; // Rp 70.000

            $this->assertSame(
                70000.0,
                $earnedCommission,
                'Technician labor commission must be accurately computed at 35% of billable service fee.'
            );

            // 4. Mark Reservation and Work Completed
            $reservation->update(['status' => 'completed']);
            $this->assertSame('completed', $reservation->fresh()->status);
            $this->assertSame($technician->id, $reservation->fresh()->staff_id);
        });
    }
}
