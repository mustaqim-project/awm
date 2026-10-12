<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\CommunityEvent;
use App\Models\CommunityEventRegistration;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CommunityEventPaymentTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private User $otherUser;
    private Business $otherBusiness;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->owner = User::create([
            'name' => 'Budi Owner Kafe',
            'email' => 'budi@kafe.test',
            'phone' => '081234567890',
            'password' => 'password',
        ]);

        $this->business = Business::create(['name' => 'Kafe Kopi Budi']);
        $this->business->users()->attach($this->owner->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $this->owner->update(['active_business_id' => $this->business->id]);

        BusinessSubscription::create([
            'business_id' => $this->business->id,
            'plan_code' => BusinessSubscription::PLAN_FREE,
            'price' => 0,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => Carbon::now()->subDays(10),
            'ends_at' => Carbon::now()->addYear(),
        ]);

        // User kedua untuk pengujian keamanan cross-user
        $this->otherUser = User::create([
            'name' => 'Siti Owner Bakery',
            'email' => 'siti@bakery.test',
            'phone' => '089876543210',
            'password' => 'password',
        ]);

        $this->otherBusiness = Business::create(['name' => 'Siti Bakery']);
        $this->otherBusiness->users()->attach($this->otherUser->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $this->otherUser->update(['active_business_id' => $this->otherBusiness->id]);

        BusinessSubscription::create([
            'business_id' => $this->otherBusiness->id,
            'plan_code' => BusinessSubscription::PLAN_STANDARD_MONTHLY,
            'price' => 199000,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => Carbon::now()->subDays(5),
            'ends_at' => Carbon::now()->addYear(),
        ]);

        Context::setBusiness($this->business);
    }

    public function test_free_event_registration_is_marked_paid_instantly(): void
    {
        $freeEvent = CommunityEvent::create([
            'title' => 'Sharing Session Komunitas Gratis',
            'slug' => 'sharing-session-gratis',
            'description' => 'Diskusi santai sesama pebisnis UMKM.',
            'event_type' => 'online',
            'price_type' => CommunityEvent::PRICE_TYPE_FREE,
            'price' => 0,
            'location_name' => 'Google Meet',
            'start_time' => Carbon::now()->addDays(3),
            'end_time' => Carbon::now()->addDays(3)->addHours(2),
            'registration_deadline' => Carbon::now()->addDays(2),
            'quota' => 100,
            'checkin_code' => 'COOCA-FREE-01',
            'status' => CommunityEvent::STATUS_PUBLISHED,
        ]);

        $response = $this->actingAs($this->owner)->post(route('community.events.register', $freeEvent));

        $response->assertRedirect(route('community.events.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('community_event_registrations', [
            'event_id' => $freeEvent->id,
            'user_id' => $this->owner->id,
            'payment_status' => CommunityEventRegistration::PAYMENT_STATUS_PAID,
            'amount_paid' => 0,
        ]);

        // Tiket langsung dapat diakses
        $ticketRes = $this->actingAs($this->owner)->getJson(route('community.events.ticket', $freeEvent));
        $ticketRes->assertStatus(200);
        $ticketRes->assertJson([
            'success' => true,
            'ticket' => [
                'event_title' => 'Sharing Session Komunitas Gratis',
                'is_paid' => false,
            ],
        ]);
    }

    public function test_paid_event_registration_creates_pending_status_and_redirects_to_qris_payment(): void
    {
        $paidEvent = CommunityEvent::create([
            'title' => 'Masterclass HPP & Food Costing',
            'slug' => 'masterclass-hpp-food-costing',
            'description' => 'Bedah tuntas kalkulasi HPP kuliner.',
            'event_type' => 'offline',
            'price_type' => CommunityEvent::PRICE_TYPE_PAID,
            'price' => 75000,
            'location_name' => 'Hotel Santika Premiere',
            'location_address' => 'Jl. Jend. Sudirman No. 19',
            'latitude' => -7.782871,
            'longitude' => 110.367093,
            'start_time' => Carbon::now()->addDays(5),
            'end_time' => Carbon::now()->addDays(5)->addHours(4),
            'registration_deadline' => Carbon::now()->addDays(4),
            'quota' => 30,
            'checkin_code' => 'COOCA-PAID-01',
            'status' => CommunityEvent::STATUS_PUBLISHED,
        ]);

        $response = $this->actingAs($this->owner)->post(route('community.events.register', $paidEvent));

        $registration = CommunityEventRegistration::where('event_id', $paidEvent->id)
            ->where('user_id', $this->owner->id)
            ->firstOrFail();

        $response->assertRedirect(route('community.events.payment.show', $registration));

        $this->assertEquals(CommunityEventRegistration::PAYMENT_STATUS_PENDING, $registration->payment_status);
        $this->assertEquals(75000.0, (float) $registration->amount_paid);
        $this->assertEquals('tripay', $registration->payment_gateway);
        $this->assertSame('qris', strtolower((string) $registration->payment_method));
        $this->assertNotEmpty($registration->gateway_reference);
        $this->assertNotEmpty($registration->gateway_qr_string);
    }

    public function test_unpaid_registration_cannot_access_ticket_and_returns_http_402(): void
    {
        $paidEvent = CommunityEvent::create([
            'title' => 'Workshop Omnichannel 2026',
            'slug' => 'workshop-omnichannel-2026',
            'description' => 'Integrasi marketplace dan offline.',
            'event_type' => 'offline',
            'price_type' => CommunityEvent::PRICE_TYPE_PAID,
            'price' => 100000,
            'location_name' => 'Auditorium Cooca HQ',
            'start_time' => Carbon::now()->addDays(4),
            'end_time' => Carbon::now()->addDays(4)->addHours(3),
            'registration_deadline' => Carbon::now()->addDays(3),
            'checkin_code' => 'COOCA-EVT-OMNI1',
            'status' => CommunityEvent::STATUS_PUBLISHED,
        ]);

        $registration = CommunityEventRegistration::create([
            'event_id' => $paidEvent->id,
            'user_id' => $this->owner->id,
            'business_id' => $this->business->id,
            'ticket_code' => 'EVT-TEST-PENDING99',
            'status' => CommunityEventRegistration::STATUS_REGISTERED,
            'payment_status' => CommunityEventRegistration::PAYMENT_STATUS_PENDING,
            'amount_paid' => 100000,
            'payment_gateway' => 'tripay',
            'payment_method' => 'qris',
            'registered_at' => Carbon::now(),
        ]);

        $ticketRes = $this->actingAs($this->owner)->getJson(route('community.events.ticket', $paidEvent));

        $ticketRes->assertStatus(402);
        $ticketRes->assertJson([
            'success' => false,
            'is_pending_payment' => true,
            'payment_url' => route('community.events.payment.show', $registration),
        ]);
    }

    public function test_unpaid_registration_cannot_checkin_via_location_or_barcode(): void
    {
        $paidEvent = CommunityEvent::create([
            'title' => 'Temu Akbar Kuliner Nusantara',
            'slug' => 'temu-akbar-kuliner-nusantara',
            'description' => 'Networking kuliner Indonesia.',
            'event_type' => 'offline',
            'price_type' => CommunityEvent::PRICE_TYPE_PAID,
            'price' => 50000,
            'location_name' => 'Jogja Expo Center',
            'latitude' => -7.799824,
            'longitude' => 110.404289,
            'checkin_radius_meters' => 300,
            'start_time' => Carbon::now()->addDays(2),
            'end_time' => Carbon::now()->addDays(2)->addHours(4),
            'registration_deadline' => Carbon::now()->addDay(),
            'checkin_code' => 'COOCA-TEMU-AKBAR',
            'status' => CommunityEvent::STATUS_PUBLISHED,
        ]);

        $registration = CommunityEventRegistration::create([
            'event_id' => $paidEvent->id,
            'user_id' => $this->owner->id,
            'business_id' => $this->business->id,
            'ticket_code' => 'EVT-UNPAID-TICKET',
            'status' => CommunityEventRegistration::STATUS_REGISTERED,
            'payment_status' => CommunityEventRegistration::PAYMENT_STATUS_PENDING,
            'amount_paid' => 50000,
            'registered_at' => Carbon::now(),
        ]);

        // Coba check-in lokasi (harus ditolak karena belum bayar)
        $locRes = $this->actingAs($this->owner)->postJson(route('community.events.checkin-location', $paidEvent), [
            'latitude' => -7.799824,
            'longitude' => 110.404289,
        ]);
        $locRes->assertStatus(422);
        $this->assertStringContainsString('belum lunas', $locRes->json('message'));

        // Coba check-in barcode (harus ditolak karena belum bayar)
        $barcodeRes = $this->actingAs($this->owner)->postJson(route('community.events.checkin-barcode', $paidEvent), [
            'code' => 'EVT-UNPAID-TICKET',
        ]);
        $barcodeRes->assertStatus(422);
        $this->assertStringContainsString('belum lunas', $barcodeRes->json('message'));
    }

    public function test_qris_payment_page_renders_with_details_and_qris_qr_code(): void
    {
        $paidEvent = CommunityEvent::create([
            'title' => 'Workshop Finansial Kafe',
            'slug' => 'workshop-finansial-kafe',
            'description' => 'Manajemen cashflow kafe.',
            'event_type' => 'offline',
            'price_type' => CommunityEvent::PRICE_TYPE_PAID,
            'price' => 85000,
            'location_name' => 'Ruang Komunitas',
            'start_time' => Carbon::now()->addDays(3),
            'end_time' => Carbon::now()->addDays(3)->addHours(3),
            'registration_deadline' => Carbon::now()->addDays(2),
            'checkin_code' => 'COOCA-EVT-FIN1',
            'status' => CommunityEvent::STATUS_PUBLISHED,
        ]);

        $registration = CommunityEventRegistration::create([
            'event_id' => $paidEvent->id,
            'user_id' => $this->owner->id,
            'business_id' => $this->business->id,
            'ticket_code' => 'EVT-FIN-999',
            'status' => CommunityEventRegistration::STATUS_REGISTERED,
            'payment_status' => CommunityEventRegistration::PAYMENT_STATUS_PENDING,
            'amount_paid' => 85000,
            'payment_gateway' => 'tripay',
            'payment_method' => 'qris',
            'gateway_qr_string' => '00020101021226670016ID.CO.TRIPAY.WWW011893600999999999999902150000000000000005204581253033605405850005802ID5910COOCA SAAS6007BANDUNG6304ABCD',
            'gateway_expired_at' => Carbon::now()->addMinutes(15),
            'registered_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($this->owner)->get(route('community.events.payment.show', $registration));

        $response->assertStatus(200);
        $response->assertSee('Pembayaran Tiket Event');
        $response->assertSee('Workshop Finansial Kafe');
        $response->assertSee('85.000');
        $response->assertSee('QRIS');
        $response->assertSee('Simulasi Bayar QRIS Sukses');
    }

    public function test_simulating_qris_payment_marks_registration_as_paid_and_enables_ticket(): void
    {
        $paidEvent = CommunityEvent::create([
            'title' => 'Pelatihan Barista Terampil',
            'slug' => 'pelatihan-barista-terampil',
            'description' => 'Teknik espresso dan latte art.',
            'event_type' => 'offline',
            'price_type' => CommunityEvent::PRICE_TYPE_PAID,
            'price' => 120000,
            'location_name' => 'Studio Kopi',
            'start_time' => Carbon::now()->addDays(6),
            'end_time' => Carbon::now()->addDays(6)->addHours(4),
            'registration_deadline' => Carbon::now()->addDays(5),
            'checkin_code' => 'COOCA-EVT-BARISTA1',
            'status' => CommunityEvent::STATUS_PUBLISHED,
        ]);

        $registration = CommunityEventRegistration::create([
            'event_id' => $paidEvent->id,
            'user_id' => $this->owner->id,
            'business_id' => $this->business->id,
            'ticket_code' => 'EVT-BARISTA-456',
            'status' => CommunityEventRegistration::STATUS_REGISTERED,
            'payment_status' => CommunityEventRegistration::PAYMENT_STATUS_PENDING,
            'amount_paid' => 120000,
            'payment_gateway' => 'tripay',
            'payment_method' => 'qris',
            'registered_at' => Carbon::now(),
        ]);

        // Eksekusi simulasi bayar
        $simResponse = $this->actingAs($this->owner)->post(route('community.events.payment.simulate', $registration));

        $simResponse->assertRedirect(route('community.events.index'));
        $simResponse->assertSessionHas('success');

        $registration->refresh();
        $this->assertTrue($registration->isPaid());
        $this->assertNotNull($registration->paid_at);

        // Setelah lunas, tiket langsung bisa diambil
        $ticketRes = $this->actingAs($this->owner)->getJson(route('community.events.ticket', $paidEvent));
        $ticketRes->assertStatus(200);
        $ticketRes->assertJson([
            'success' => true,
            'ticket' => [
                'ticket_code' => 'EVT-BARISTA-456',
                'event_title' => 'Pelatihan Barista Terampil',
            ],
        ]);

        // Halaman pembayaran akan mengarahkan balik ke events jika sudah lunas
        $payPageRes = $this->actingAs($this->owner)->get(route('community.events.payment.show', $registration));
        $payPageRes->assertRedirect(route('community.events.index'));
    }

    public function test_payment_status_polling_endpoint_returns_json_status(): void
    {
        $paidEvent = CommunityEvent::create([
            'title' => 'Seminar Akuntansi Praktis',
            'slug' => 'seminar-akuntansi-praktis',
            'description' => 'Laporan keuangan sederhana.',
            'event_type' => 'online',
            'price_type' => CommunityEvent::PRICE_TYPE_PAID,
            'price' => 60000,
            'location_name' => 'Zoom',
            'start_time' => Carbon::now()->addDays(3),
            'end_time' => Carbon::now()->addDays(3)->addHours(2),
            'registration_deadline' => Carbon::now()->addDays(2),
            'checkin_code' => 'COOCA-EVT-ACC1',
            'status' => CommunityEvent::STATUS_PUBLISHED,
        ]);

        $registration = CommunityEventRegistration::create([
            'event_id' => $paidEvent->id,
            'user_id' => $this->owner->id,
            'business_id' => $this->business->id,
            'ticket_code' => 'EVT-ACC-789',
            'status' => CommunityEventRegistration::STATUS_REGISTERED,
            'payment_status' => CommunityEventRegistration::PAYMENT_STATUS_PENDING,
            'amount_paid' => 60000,
            'registered_at' => Carbon::now(),
        ]);

        // Cek status saat masih pending
        $statusRes = $this->actingAs($this->owner)->getJson(route('community.events.payment.status', $registration));
        $statusRes->assertStatus(200);
        $statusRes->assertJson([
            'success' => true,
            'is_paid' => false,
            'payment_status' => 'pending',
        ]);

        // Tandai lunas
        $registration->markAsPaid();

        // Cek kembali status polling
        $statusRes2 = $this->actingAs($this->owner)->getJson(route('community.events.payment.status', $registration));
        $statusRes2->assertStatus(200);
        $statusRes2->assertJson([
            'success' => true,
            'is_paid' => true,
            'payment_status' => 'paid',
            'redirect_url' => route('community.events.index'),
        ]);
    }

    public function test_user_cannot_access_or_pay_for_another_users_registration(): void
    {
        $paidEvent = CommunityEvent::create([
            'title' => 'Workshop Rahasia UMKM',
            'slug' => 'workshop-rahasia-umkm',
            'description' => 'Strategi ekspansi bisnis.',
            'event_type' => 'offline',
            'price_type' => CommunityEvent::PRICE_TYPE_PAID,
            'price' => 150000,
            'location_name' => 'Hotel Tentrem',
            'start_time' => Carbon::now()->addDays(7),
            'end_time' => Carbon::now()->addDays(7)->addHours(3),
            'registration_deadline' => Carbon::now()->addDays(6),
            'checkin_code' => 'COOCA-EVT-RAHASIA1',
            'status' => CommunityEvent::STATUS_PUBLISHED,
        ]);

        $registrationOwner = CommunityEventRegistration::create([
            'event_id' => $paidEvent->id,
            'user_id' => $this->owner->id,
            'business_id' => $this->business->id,
            'ticket_code' => 'EVT-RAHASIA-1',
            'status' => CommunityEventRegistration::STATUS_REGISTERED,
            'payment_status' => CommunityEventRegistration::PAYMENT_STATUS_PENDING,
            'amount_paid' => 150000,
            'registered_at' => Carbon::now(),
        ]);

        Context::setBusiness($this->otherBusiness);

        // User lain mencoba membuka halaman pembayaran user owner -> Ditolak (403)
        $forbiddenPage = $this->actingAs($this->otherUser)->get(route('community.events.payment.show', $registrationOwner));
        $forbiddenPage->assertStatus(403);

        // User lain mencoba menyimulasikan bayar tiket orang lain -> Ditolak (403)
        $forbiddenPay = $this->actingAs($this->otherUser)->post(route('community.events.payment.simulate', $registrationOwner));
        $forbiddenPay->assertStatus(403);
    }
}
