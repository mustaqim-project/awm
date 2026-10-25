<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
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

class CommunityEventFeatureTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private Admin $admin;

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
        Context::setBusiness($this->business);

        // Give business a Free subscription to verify community is active for all subscription types
        BusinessSubscription::create([
            'business_id' => $this->business->id,
            'plan_code' => BusinessSubscription::PLAN_FREE,
            'price' => 0,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => Carbon::now()->subDays(10),
            'ends_at' => Carbon::now()->addYear(),
        ]);

        $this->admin = Admin::create([
            'name' => 'Super Admin Cooca',
            'email' => 'superadmin@cooca.test',
            'password' => 'password',
            'role' => 'super_admin',
            'is_active' => true,
        ]);
    }

    public function test_community_displays_upcoming_events_and_filters_out_past_events(): void
    {
        // 1. Upcoming Event
        $upcoming = CommunityEvent::create([
            'title' => 'Workshop Kasir POS UMKM 2026',
            'slug' => 'workshop-kasir-pos-2026',
            'description' => 'Pelatihan optimasi omset kasir.',
            'event_type' => 'offline',
            'location_name' => 'Hotel Santika Premiere',
            'location_address' => 'Jl. Jend. Sudirman No. 19',
            'latitude' => -7.782871,
            'longitude' => 110.367093,
            'checkin_radius_meters' => 250,
            'start_time' => Carbon::now()->addDays(2)->setHour(9)->setMinute(0),
            'end_time' => Carbon::now()->addDays(2)->setHour(12)->setMinute(0),
            'registration_deadline' => Carbon::now()->addDay(),
            'quota' => 50,
            'checkin_code' => 'COOCA-EVT-UPCOMING1',
            'status' => CommunityEvent::STATUS_PUBLISHED,
        ]);

        // 2. Past Event (Yang sudah lewat jangan tampilkan!)
        $past = CommunityEvent::create([
            'title' => 'Meetup Bisnis Kuno Yang Sudah Lewat',
            'slug' => 'meetup-bisnis-kuno',
            'description' => 'Event lama bulan lalu.',
            'event_type' => 'offline',
            'location_name' => 'Gedung Lama',
            'start_time' => Carbon::now()->subDays(10),
            'end_time' => Carbon::now()->subDays(10)->addHours(3),
            'registration_deadline' => Carbon::now()->subDays(12),
            'checkin_code' => 'COOCA-EVT-PAST1',
            'status' => CommunityEvent::STATUS_PUBLISHED,
        ]);

        $response = $this->actingAs($this->owner)->get(route('community.index'));

        $response->assertStatus(200);
        // Upcoming event should be visible at top
        $response->assertSee('Event &amp; Agenda Komunitas Terbaru', false);
        $response->assertSee('Workshop Kasir POS UMKM 2026');
        $response->assertSee('Hotel Santika Premiere');
        $response->assertSee('Aktif Semua Paket');

        // Past event should NOT be visible in upcoming feed
        $response->assertDontSee('Meetup Bisnis Kuno Yang Sudah Lewat');
    }

    public function test_user_can_register_for_event_and_receives_unique_ticket_code(): void
    {
        $event = CommunityEvent::create([
            'title' => 'Seminar Strategi Finansial ERP',
            'slug' => 'seminar-finansial-erp',
            'description' => 'Manajemen laba rugi kasir.',
            'event_type' => 'offline',
            'location_name' => 'Ballroom Cooca',
            'start_time' => Carbon::now()->addDays(3),
            'end_time' => Carbon::now()->addDays(3)->addHours(2),
            'registration_deadline' => Carbon::now()->addDays(2),
            'quota' => 20,
            'checkin_code' => 'COOCA-EVT-FIN1',
            'status' => CommunityEvent::STATUS_PUBLISHED,
        ]);

        $response = $this->actingAs($this->owner)->post(route('community.events.register', $event));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('community_event_registrations', [
            'event_id' => $event->id,
            'user_id' => $this->owner->id,
            'business_id' => $this->business->id,
            'status' => CommunityEventRegistration::STATUS_REGISTERED,
        ]);

        $registration = CommunityEventRegistration::where('event_id', $event->id)->firstOrFail();
        $this->assertStringStartsWith('EVT-', $registration->ticket_code);
    }

    public function test_user_can_cancel_event_registration(): void
    {
        $event = CommunityEvent::create([
            'title' => 'Webinar Digital Marketing',
            'slug' => 'webinar-digital-marketing',
            'description' => 'Materi marketing medsos.',
            'event_type' => 'online',
            'location_name' => 'Zoom',
            'start_time' => Carbon::now()->addDays(4),
            'end_time' => Carbon::now()->addDays(4)->addHours(2),
            'registration_deadline' => Carbon::now()->addDays(3),
            'checkin_code' => 'COOCA-EVT-MKT1',
            'status' => CommunityEvent::STATUS_PUBLISHED,
        ]);

        $reg = CommunityEventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $this->owner->id,
            'business_id' => $this->business->id,
            'ticket_code' => 'EVT-TEST-123456',
            'registered_at' => Carbon::now(),
            'status' => CommunityEventRegistration::STATUS_REGISTERED,
        ]);

        $response = $this->actingAs($this->owner)->post(route('community.events.cancel', $event));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('community_event_registrations', [
            'id' => $reg->id,
            'status' => CommunityEventRegistration::STATUS_CANCELLED,
        ]);
    }

    public function test_user_can_view_event_ticket_details_with_svg_codes(): void
    {
        $event = CommunityEvent::create([
            'title' => 'Kopi Darat Owner F&B',
            'slug' => 'kopdar-owner-fnb',
            'description' => 'Diskusi resep HPP & stok.',
            'event_type' => 'offline',
            'location_name' => 'Kafe Sentral Jogja',
            'start_time' => Carbon::now()->addDays(5),
            'end_time' => Carbon::now()->addDays(5)->addHours(3),
            'registration_deadline' => Carbon::now()->addDays(4),
            'checkin_code' => 'COOCA-EVT-FNB1',
            'status' => CommunityEvent::STATUS_PUBLISHED,
        ]);

        $reg = CommunityEventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $this->owner->id,
            'business_id' => $this->business->id,
            'ticket_code' => 'EVT-TEST-FNB789',
            'registered_at' => Carbon::now(),
            'status' => CommunityEventRegistration::STATUS_REGISTERED,
        ]);

        $response = $this->actingAs($this->owner)->getJson(route('community.events.ticket', $event));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'ticket' => [
                'ticket_code' => 'EVT-TEST-FNB789',
                'event_title' => 'Kopi Darat Owner F&B',
                'is_checked_in' => false,
            ],
        ]);

        $ticket = $response->json('ticket');
        $this->assertStringContainsString('<svg', $ticket['qr_code_svg']);
        $this->assertStringContainsString('<svg', $ticket['barcode_svg']);
    }

    public function test_checkin_via_location_gps_succeeds_within_radius(): void
    {
        // Event venue at coordinates: -7.782871, 110.367093 with radius 200m
        $event = CommunityEvent::create([
            'title' => 'Meetup Akbar Pengusaha Retail',
            'slug' => 'meetup-akbar-retail',
            'description' => 'Pelatihan barcode kasir.',
            'event_type' => 'offline',
            'location_name' => 'Jogja Expo Center',
            'latitude' => -7.782871,
            'longitude' => 110.367093,
            'checkin_radius_meters' => 200,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addHours(4),
            'registration_deadline' => Carbon::now()->addHours(12),
            'checkin_code' => 'COOCA-EVT-RETAIL',
            'status' => CommunityEvent::STATUS_PUBLISHED,
        ]);

        $reg = CommunityEventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $this->owner->id,
            'business_id' => $this->business->id,
            'ticket_code' => 'EVT-RETAIL-001',
            'registered_at' => Carbon::now(),
            'status' => CommunityEventRegistration::STATUS_REGISTERED,
        ]);

        // Participant is ~50 meters away: -7.782500, 110.367093
        $response = $this->actingAs($this->owner)->postJson(route('community.events.checkin-location', $event), [
            'latitude' => -7.782500,
            'longitude' => 110.367093,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $reg->refresh();
        $this->assertTrue($reg->isCheckedIn());
        $this->assertSame(CommunityEventRegistration::METHOD_LOCATION, $reg->checkin_method);
    }

    public function test_checkin_via_location_gps_fails_if_outside_radius(): void
    {
        $event = CommunityEvent::create([
            'title' => 'Workshop Retail Luar Kota',
            'slug' => 'workshop-retail-luar-kota',
            'description' => 'Test jarak GPS.',
            'event_type' => 'offline',
            'location_name' => 'Venue Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'checkin_radius_meters' => 150,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addHours(4),
            'registration_deadline' => Carbon::now()->addHours(12),
            'checkin_code' => 'COOCA-EVT-JKT',
            'status' => CommunityEvent::STATUS_PUBLISHED,
        ]);

        $reg = CommunityEventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $this->owner->id,
            'business_id' => $this->business->id,
            'ticket_code' => 'EVT-JKT-001',
            'registered_at' => Carbon::now(),
            'status' => CommunityEventRegistration::STATUS_REGISTERED,
        ]);

        // Participant is in Jogja (~400 km away)
        $response = $this->actingAs($this->owner)->postJson(route('community.events.checkin-location', $event), [
            'latitude' => -7.782871,
            'longitude' => 110.367093,
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
        $this->assertStringContainsString('meter dari venue', $response->json('message'));

        $reg->refresh();
        $this->assertFalse($reg->isCheckedIn());
    }

    public function test_checkin_via_barcode_succeeds(): void
    {
        $event = CommunityEvent::create([
            'title' => 'Masterclass POS Multi-Outlet',
            'slug' => 'masterclass-pos-multi-outlet',
            'description' => 'Integrasi barcode & scanner.',
            'event_type' => 'offline',
            'location_name' => 'Kantor Cooca',
            'start_time' => Carbon::now()->addDays(2),
            'end_time' => Carbon::now()->addDays(2)->addHours(3),
            'registration_deadline' => Carbon::now()->addDay(),
            'checkin_code' => 'COOCA-EVT-MASTER123',
            'status' => CommunityEvent::STATUS_PUBLISHED,
        ]);

        $reg = CommunityEventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $this->owner->id,
            'business_id' => $this->business->id,
            'ticket_code' => 'EVT-MASTER-999',
            'registered_at' => Carbon::now(),
            'status' => CommunityEventRegistration::STATUS_REGISTERED,
        ]);

        // Participant checks in via event barcode code
        $response = $this->actingAs($this->owner)->postJson(route('community.events.checkin-barcode', $event), [
            'code' => 'COOCA-EVT-MASTER123',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $reg->refresh();
        $this->assertTrue($reg->isCheckedIn());
        $this->assertSame(CommunityEventRegistration::METHOD_BARCODE, $reg->checkin_method);
    }

    public function test_admin_can_manage_events_and_checkin_participants(): void
    {
        // 1. Admin creates event
        $createResponse = $this->actingAs($this->admin, 'admin')->post(route('admin.events.store'), [
            'title' => 'Event Dari Admin',
            'description' => 'Deskripsi event dari admin.',
            'event_type' => 'offline',
            'location_name' => 'Auditorium Cooca',
            'location_address' => 'Jl. Kaliurang KM 5',
            'latitude' => -7.7600,
            'longitude' => 110.3800,
            'checkin_radius_meters' => 300,
            'start_time' => Carbon::now()->addDays(3)->format('Y-m-d H:i:s'),
            'end_time' => Carbon::now()->addDays(3)->addHours(2)->format('Y-m-d H:i:s'),
            'registration_deadline' => Carbon::now()->addDays(2)->format('Y-m-d H:i:s'),
            'quota' => 40,
            'status' => 'published',
        ]);

        $createResponse->assertStatus(302);
        $event = CommunityEvent::where('title', 'Event Dari Admin')->firstOrFail();

        // 2. Register participant
        $reg = CommunityEventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $this->owner->id,
            'business_id' => $this->business->id,
            'ticket_code' => 'EVT-ADMIN-SCAN01',
            'registered_at' => Carbon::now(),
            'status' => CommunityEventRegistration::STATUS_REGISTERED,
        ]);

        // 3. Admin scans participant barcode ticket
        $scanResponse = $this->actingAs($this->admin, 'admin')->postJson(route('admin.events.checkin', $event), [
            'ticket_code' => 'EVT-ADMIN-SCAN01',
        ]);

        $scanResponse->assertStatus(200);
        $scanResponse->assertJson(['success' => true]);

        $reg->refresh();
        $this->assertTrue($reg->isCheckedIn());
        $this->assertSame(CommunityEventRegistration::METHOD_BARCODE, $reg->checkin_method);

        // 4. Admin exports CSV
        $exportResponse = $this->actingAs($this->admin, 'admin')->get(route('admin.events.export', $event));
        $exportResponse->assertStatus(200);
        $this->assertStringContainsString('text/csv', $exportResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('EVT-ADMIN-SCAN01', $exportResponse->getContent());
        $this->assertStringContainsString($this->owner->name, $exportResponse->getContent());
    }

    public function test_paid_and_online_events_functionality_and_ticket_payload(): void
    {
        // 1. Create a paid online event
        $paidOnlineEvent = CommunityEvent::create([
            'title' => 'Masterclass Keuangan Berbayar Online',
            'slug' => 'masterclass-keuangan-berbayar-online',
            'description' => 'Materi eksklusif manajemen arus kas dan HPP.',
            'event_type' => CommunityEvent::TYPE_ONLINE,
            'price_type' => CommunityEvent::PRICE_TYPE_PAID,
            'price' => 149000,
            'online_meeting_url' => 'https://meet.google.com/cooca-paid-test',
            'location_name' => 'Google Meet Live',
            'start_time' => Carbon::now()->addDays(5)->setHour(19)->setMinute(0),
            'end_time' => Carbon::now()->addDays(5)->setHour(21)->setMinute(0),
            'registration_deadline' => Carbon::now()->addDays(4),
            'quota' => 100,
            'checkin_code' => 'COOCA-EVT-PAIDONLINE',
            'status' => CommunityEvent::STATUS_PUBLISHED,
        ]);

        $this->assertTrue($paidOnlineEvent->isPaid());
        $this->assertFalse($paidOnlineEvent->isFree());
        $this->assertTrue($paidOnlineEvent->isOnline());
        $this->assertFalse($paidOnlineEvent->isOffline());
        $this->assertSame('Rp 149.000', $paidOnlineEvent->formatted_price);

        // 2. Register for paid event (mengarahkan ke instruksi pembayaran QRIS)
        $regResponse = $this->actingAs($this->owner)->post(route('community.events.register', $paidOnlineEvent));

        $registration = CommunityEventRegistration::where('event_id', $paidOnlineEvent->id)
            ->where('user_id', $this->owner->id)
            ->firstOrFail();

        $regResponse->assertRedirect(route('community.events.payment.show', $registration));
        $this->assertEquals(149000, $registration->amount_paid);
        $this->assertEquals(CommunityEventRegistration::PAYMENT_STATUS_PENDING, $registration->payment_status);

        // Sebelum bayar, akses tiket ditolak dengan HTTP 402
        $unpaidTicketResponse = $this->actingAs($this->owner)->getJson(route('community.events.ticket', $paidOnlineEvent));
        $unpaidTicketResponse->assertStatus(402);

        // Selesaikan pembayaran QRIS via simulation / markAsPaid
        $registration->markAsPaid();

        // 3. Inspect Ticket payload via JSON API setelah pembayaran lunas
        $ticketResponse = $this->actingAs($this->owner)->getJson(route('community.events.ticket', $paidOnlineEvent));
        $ticketResponse->assertStatus(200);
        $ticketResponse->assertJson([
            'success' => true,
            'ticket' => [
                'event_title' => 'Masterclass Keuangan Berbayar Online',
                'event_type' => 'online',
                'is_online' => true,
                'is_offline' => false,
                'price_type' => 'paid',
                'is_paid' => true,
                'price' => 149000,
                'formatted_price' => 'Rp 149.000',
                'online_meeting_url' => 'https://meet.google.com/cooca-paid-test',
            ],
        ]);

        // 4. View events catalog page and index feed
        $catalogResponse = $this->actingAs($this->owner)->get(route('community.events.index'));
        $catalogResponse->assertStatus(200);
        $catalogResponse->assertSee('Masterclass Keuangan Berbayar Online');
        $catalogResponse->assertSee('Rp 149.000');
        $catalogResponse->assertSee('online');

        $feedResponse = $this->actingAs($this->owner)->get(route('community.index'));
        $feedResponse->assertStatus(200);
        $feedResponse->assertSee('Masterclass Keuangan Berbayar Online');
        $feedResponse->assertSee('Rp 149.000');
    }
}
