<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\CommerceReservation;
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

class CommerceReservationIndustryAndDpTest extends TestCase
{
    use RefreshDatabase;

    private User $ownerUser;
    private Business $business;
    private Location $location;
    private User $staffUser;
    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);
        $this->seed(RbacSeeder::class);

        $this->ownerUser = User::create([
            'name' => 'Owner Multi Industri',
            'email' => 'owner@industri.test',
            'phone' => '081199990001',
            'password' => bcrypt('password123'),
        ]);
        $this->ownerUser->forceFill(['email_verified_at' => now()])->save();

        $this->staffUser = User::create([
            'name' => 'Budi Mekanik & Barber',
            'email' => 'staff@industri.test',
            'phone' => '081199990002',
            'password' => bcrypt('password123'),
        ]);
        $this->staffUser->forceFill(['email_verified_at' => now()])->save();

        $this->business = Business::create([
            'name' => 'Sentra Layanan & Resto Cooca',
            'slug' => 'sentra-layanan-cooca',
            'template_code' => 'fnb_resto',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->ownerUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $this->business->users()->attach($this->staffUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
        ]);

        $this->ownerUser->update(['active_business_id' => $this->business->id]);
        $this->staffUser->update(['active_business_id' => $this->business->id]);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Cabang Utama Bandung',
            'code' => 'BDG-01',
            'is_active' => true,
        ]);

        $this->unit = Unit::firstOrFail();
        Context::setBusiness($this->business);
    }

    public function test_owner_can_create_manual_reservation_with_dp_payment(): void
    {
        $table = PosTable::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'name' => 'VIP Lounge 01',
            'table_number' => 'VIP-01',
            'capacity' => 8,
            'status' => 'available',
            'is_active' => true,
        ]);

        $payload = [
            'customer_name' => 'Ahmad Suhendar',
            'customer_phone' => '081234567890',
            'customer_email' => 'ahmad@example.com',
            'reservation_date' => date('Y-m-d', strtotime('+1 day')),
            'time_slot' => '18:30',
            'guest_count' => 6,
            'location_id' => $this->location->id,
            'pos_table_id' => $table->id,
            'estimated_total' => 1500000,
            'dp_amount' => 500000,
            'dp_payment_method' => 'qris',
            'dp_payment_reference' => 'QRIS-12345678',
            'notes' => 'Ulang tahun keluarga, request lilin kue',
        ];

        $response = $this->actingAs($this->ownerUser)
            ->withSession([
                'active_business_id' => $this->business->id,
                'auth_wa_otp_verified_user_id' => $this->ownerUser->id,
            ])
            ->post(route('storefront.reservations.store'), $payload);

        $response->assertRedirect(route('storefront.reservations.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('commerce_reservations', [
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'customer_name' => 'Ahmad Suhendar',
            'pos_table_id' => $table->id,
            'guest_count' => 6,
            'status' => CommerceReservation::STATUS_CONFIRMED,
            'source' => CommerceReservation::SOURCE_MANUAL,
            'payment_status' => CommerceReservation::PAYMENT_DP_PAID,
            'dp_payment_method' => 'qris',
            'dp_payment_reference' => 'QRIS-12345678',
        ]);

        $reservation = CommerceReservation::where('customer_name', 'Ahmad Suhendar')->first();
        $this->assertNotNull($reservation);
        $this->assertEquals(500000.0, (float) $reservation->dp_amount);
        $this->assertEquals(1500000.0, (float) $reservation->estimated_total);
        $this->assertTrue($reservation->isDpPaid());
        $this->assertFalse($reservation->isFullyPaid());
        $this->assertEquals(1000000.0, $reservation->remainingBalance());
    }

    public function test_workshop_manual_reservation_with_vehicle_and_mechanic_assignment(): void
    {
        $workshopBiz = Business::create([
            'name' => 'Cooca Bengkel Presisi',
            'slug' => 'cooca-bengkel-presisi',
            'template_code' => 'service_workshop',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        $workshopBiz->users()->attach($this->ownerUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $workshopBiz->users()->attach($this->staffUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
        ]);
        $this->ownerUser->update(['active_business_id' => $workshopBiz->id]);
        Context::setBusiness($workshopBiz);

        $serviceProduct = Product::create([
            'business_id' => $workshopBiz->id,
            'name' => 'Tune Up & Flushing Rem',
            'slug' => 'tune-up-flushing-rem',
            'type' => 'service',
            'output_unit_id' => $this->unit->id,
            'selling_price' => 350000,
            'is_active' => true,
        ]);

        $payload = [
            'customer_name' => 'Rian Hidayat',
            'customer_phone' => '087711223344',
            'reservation_date' => date('Y-m-d', strtotime('+2 days')),
            'time_slot' => '10:00',
            'product_id' => $serviceProduct->id,
            'staff_id' => $this->staffUser->id,
            'vehicle_plate' => 'D 1234 ABC',
            'vehicle_model' => 'Honda Jazz RS 2020',
            'notes' => 'Rem bunyi mencit saat turunan',
        ];

        $response = $this->actingAs($this->ownerUser)
            ->withSession([
                'active_business_id' => $workshopBiz->id,
                'auth_wa_otp_verified_user_id' => $this->ownerUser->id,
            ])
            ->post(route('storefront.reservations.store'), $payload);

        $response->assertRedirect(route('storefront.reservations.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('commerce_reservations', [
            'business_id' => $workshopBiz->id,
            'customer_name' => 'Rian Hidayat',
            'vehicle_plate' => 'D 1234 ABC',
            'vehicle_model' => 'Honda Jazz RS 2020',
            'staff_id' => $this->staffUser->id,
            'product_id' => $serviceProduct->id,
            'source' => CommerceReservation::SOURCE_MANUAL,
            'payment_status' => CommerceReservation::PAYMENT_UNPAID,
        ]);
    }

    public function test_barbershop_manual_reservation_with_duration_and_stylist(): void
    {
        $barberBiz = Business::create([
            'name' => 'Cooca Barber Studio',
            'slug' => 'cooca-barber-studio',
            'template_code' => 'service_barbershop',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        $barberBiz->users()->attach($this->ownerUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $barberBiz->users()->attach($this->staffUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
        ]);
        $this->ownerUser->update(['active_business_id' => $barberBiz->id]);
        Context::setBusiness($barberBiz);

        $haircutService = Product::create([
            'business_id' => $barberBiz->id,
            'name' => 'Gentleman Haircut & Hot Towel',
            'slug' => 'gentleman-haircut-hot-towel',
            'type' => 'service',
            'output_unit_id' => $this->unit->id,
            'selling_price' => 85000,
            'is_active' => true,
        ]);

        $payload = [
            'customer_name' => 'Faris Pratama',
            'customer_phone' => '089912345678',
            'reservation_date' => date('Y-m-d', strtotime('+1 day')),
            'time_slot' => '14:00',
            'product_id' => $haircutService->id,
            'staff_id' => $this->staffUser->id,
            'service_duration_minutes' => 45,
            'estimated_total' => 85000,
            'dp_amount' => 85000,
            'dp_payment_method' => 'cash',
            'notes' => 'Gaya fade low crop',
        ];

        $response = $this->actingAs($this->ownerUser)
            ->withSession([
                'active_business_id' => $barberBiz->id,
                'auth_wa_otp_verified_user_id' => $this->ownerUser->id,
            ])
            ->post(route('storefront.reservations.store'), $payload);

        $response->assertRedirect(route('storefront.reservations.index'));

        $this->assertDatabaseHas('commerce_reservations', [
            'business_id' => $barberBiz->id,
            'customer_name' => 'Faris Pratama',
            'staff_id' => $this->staffUser->id,
            'product_id' => $haircutService->id,
            'service_duration_minutes' => 45,
            'payment_status' => CommerceReservation::PAYMENT_PAID_IN_FULL,
            'dp_payment_method' => 'cash',
        ]);
    }

    public function test_can_record_dp_payment_on_existing_unpaid_reservation(): void
    {
        $reservation = CommerceReservation::create([
            'business_id' => $this->business->id,
            'reservation_code' => 'RESV-TEST-001',
            'customer_name' => 'Ibu Maya',
            'customer_phone' => '082100011122',
            'reservation_date' => date('Y-m-d', strtotime('+3 days')),
            'time_slot' => '19:00',
            'guest_count' => 4,
            'status' => CommerceReservation::STATUS_PENDING_CONFIRMATION,
            'payment_status' => CommerceReservation::PAYMENT_UNPAID,
            'estimated_total' => 800000,
        ]);

        $this->assertFalse($reservation->isDpPaid());

        $payload = [
            'dp_amount' => 300000,
            'dp_payment_method' => 'transfer',
            'dp_payment_reference' => 'BCA-TRF-998877',
            'notes' => 'Transfer konfirmasi via WA',
        ];

        $response = $this->actingAs($this->ownerUser)
            ->withSession([
                'active_business_id' => $this->business->id,
                'auth_wa_otp_verified_user_id' => $this->ownerUser->id,
            ])
            ->post(route('storefront.reservations.record_dp', $reservation->id), $payload);

        $response->assertRedirect(route('storefront.reservations.index'));
        $response->assertSessionHas('success');

        $reservation->refresh();
        $this->assertEquals(CommerceReservation::PAYMENT_DP_PAID, $reservation->payment_status);
        $this->assertEquals(300000.0, (float) $reservation->dp_amount);
        $this->assertEquals('transfer', $reservation->dp_payment_method);
        $this->assertEquals('BCA-TRF-998877', $reservation->dp_payment_reference);
        $this->assertNotNull($reservation->dp_paid_at);
        $this->assertEquals(500000.0, $reservation->remainingBalance());
    }

    public function test_cashier_can_create_manual_reservation_from_pos_terminal(): void
    {
        $table = PosTable::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'name' => 'Meja Teras 02',
            'table_number' => 'T-02',
            'capacity' => 4,
            'status' => 'available',
            'is_active' => true,
        ]);

        $payload = [
            'customer_name' => 'Walk-in Pelanggan POS',
            'customer_phone' => '081388887777',
            'reservation_date' => date('Y-m-d'),
            'time_slot' => '20:00',
            'guest_count' => 3,
            'location_id' => $this->location->id,
            'pos_table_id' => $table->id,
            'dp_amount' => 100000,
            'dp_payment_method' => 'cash',
            'notes' => 'Pesan langsung ke kasir',
        ];

        $response = $this->actingAs($this->ownerUser)
            ->withSession([
                'active_business_id' => $this->business->id,
                'auth_wa_otp_verified_user_id' => $this->ownerUser->id,
            ])
            ->postJson(route('pos.reservations.manual'), $payload);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('commerce_reservations', [
            'business_id' => $this->business->id,
            'customer_name' => 'Walk-in Pelanggan POS',
            'source' => CommerceReservation::SOURCE_POS,
            'payment_status' => CommerceReservation::PAYMENT_DP_PAID,
            'dp_payment_method' => 'cash',
        ]);
    }
}
