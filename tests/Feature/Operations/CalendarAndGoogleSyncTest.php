<?php

declare(strict_types=1);

namespace Tests\Feature\Operations;

use App\Domain\Operations\Models\CalendarEvent;
use App\Domain\Operations\Models\EventReminder;
use App\Domain\Operations\Models\GoogleCalendarAccount;
use App\Domain\Operations\Models\Task;
use App\Domain\Operations\Services\EventReminderService;
use App\Domain\Operations\Services\GoogleCalendarSyncService;
use App\Models\Business;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class CalendarAndGoogleSyncTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(RbacSeeder::class);

        $this->owner = User::create([
            'name' => 'Owner Kalender Test',
            'email' => 'calowner_' . uniqid() . '@test.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'phone' => '081234567890',
        ]);

        $this->business = Business::create([
            'name' => 'Bisnis Kalender Test',
            'slug' => 'bisnis-kalender-test-' . uniqid(),
            'email' => 'contact_' . uniqid() . '@test.com',
            'phone' => '081234567890',
            'address' => 'Jl. Sudirman No. 10 Jakarta',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);
    }

    public function test_can_view_calendar_index_and_events_json(): void
    {
        $event = CalendarEvent::create([
            'business_id' => $this->business->id,
            'title' => 'Meeting Mingguan Operasional',
            'start_time' => now()->startOfDay()->addHours(10),
            'end_time' => now()->startOfDay()->addHours(11),
            'color_tag' => '#007AFF',
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('operations.calendar.index'));

        $response->assertOk();
        $response->assertSee(__('operations.calendar_title'));

        // Query JSON events endpoint
        $jsonResponse = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson(route('operations.calendar.events'));

        $jsonResponse->assertOk();
        $jsonResponse->assertJsonFragment([
            'title' => 'Meeting Mingguan Operasional',
            'color' => '#007AFF',
        ]);
    }

    public function test_can_create_calendar_event_with_reminders(): void
    {
        $payload = [
            'title' => 'Presentasi Proyek Klien B',
            'description' => 'Demo fitur POS dan kalender kepada stakeholder',
            'location' => 'Ruang Meeting Lt. 2',
            'meeting_link' => 'https://meet.google.com/abc-defg-hij',
            'start_time' => now()->addDays(1)->setHour(14)->setMinute(0)->format('Y-m-d H:i:s'),
            'end_time' => now()->addDays(1)->setHour(15)->setMinute(0)->format('Y-m-d H:i:s'),
            'color_tag' => '#34C759',
            'reminders' => [
                [
                    'channel' => 'whatsapp',
                    'minutes_before' => 15,
                    'recipient_phone' => '081234567890',
                ],
                [
                    'channel' => 'email',
                    'minutes_before' => 60,
                    'recipient_email' => 'owner@test.com',
                ],
            ],
        ];

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('operations.calendar.events.store'), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('calendar_events', [
            'business_id' => $this->business->id,
            'title' => 'Presentasi Proyek Klien B',
            'location' => 'Ruang Meeting Lt. 2',
        ]);

        $event = CalendarEvent::where('title', 'Presentasi Proyek Klien B')->first();
        $this->assertNotNull($event);
        $this->assertCount(2, $event->reminders);
    }

    public function test_dispatches_due_event_reminders(): void
    {
        Mail::fake();

        $event = CalendarEvent::create([
            'business_id' => $this->business->id,
            'title' => 'Kickoff Proyek',
            'start_time' => now()->addMinutes(10),
            'end_time' => now()->addMinutes(70),
            'status' => 'scheduled',
        ]);

        $reminder = EventReminder::create([
            'calendar_event_id' => $event->id,
            'channel' => 'email',
            'minutes_before' => 15,
            'remind_at' => now()->subMinute(), // already due
            'recipient_email' => 'klien@example.com',
            'status' => 'pending',
        ]);

        $service = app(EventReminderService::class);
        $dispatched = $service->dispatchDueReminders();

        $this->assertEquals(1, $dispatched);
        $this->assertEquals('sent', $reminder->fresh()->status);
        $this->assertNotNull($reminder->fresh()->sent_at);

        // Test artisan command executes successfully
        $exitCode = $this->artisan('operations:send-event-reminders');
        $exitCode->assertExitCode(0);
    }

    public function test_google_calendar_oauth_callback_exchanges_token_and_encrypts(): void
    {
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'ya29.test_access_token_12345',
                'refresh_token' => '1//test_refresh_token_67890',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ], 200),
            'https://www.googleapis.com/oauth2/v2/userinfo' => Http::response([
                'id' => 'google_user_998877',
                'email' => 'merchant@gmail.com',
                'name' => 'Merchant Google',
                'picture' => 'https://lh3.googleusercontent.com/avatar.jpg',
            ], 200),
            'https://www.googleapis.com/calendar/v3/calendars/primary/events*' => Http::response([
                'items' => [],
            ], 200),
        ]);

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('operations.calendar.google.callback', ['code' => 'test_auth_code_xyz']));

        $response->assertRedirect(route('operations.calendar.index'));

        $account = GoogleCalendarAccount::where('business_id', $this->business->id)->first();
        $this->assertNotNull($account);
        $this->assertEquals('merchant@gmail.com', $account->email);
        $this->assertTrue($account->is_active);

        // Verify token decryption accessor
        $this->assertEquals('ya29.test_access_token_12345', $account->access_token);
        $this->assertEquals('1//test_refresh_token_67890', $account->refresh_token);
    }

    public function test_pushes_event_to_google_calendar_api(): void
    {
        Http::fake([
            'https://www.googleapis.com/calendar/v3/calendars/primary/events' => Http::response([
                'id' => 'google_event_id_12345',
                'etag' => '"etag_test_123"',
                'htmlLink' => 'https://calendar.google.com/event?eid=123',
            ], 200),
        ]);

        $account = GoogleCalendarAccount::create([
            'business_id' => $this->business->id,
            'google_id' => 'google_user_998877',
            'email' => 'merchant@gmail.com',
            'access_token' => 'valid_token_abc',
            'token_expires_at' => now()->addHour(),
            'is_active' => true,
        ]);

        $event = CalendarEvent::create([
            'business_id' => $this->business->id,
            'title' => 'Meeting Eksternal Google',
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHour(),
            'status' => 'scheduled',
        ]);

        $syncService = app(GoogleCalendarSyncService::class);
        $success = $syncService->pushEvent($event);

        $this->assertTrue($success);
        $this->assertEquals('google_event_id_12345', $event->fresh()->google_event_id);
        $this->assertEquals('synced', $event->fresh()->sync_status);
        $this->assertNotNull($event->fresh()->last_synced_at);
    }

    public function test_pulls_events_from_google_calendar_with_loop_prevention(): void
    {
        Http::fake([
            '*googleapis.com/calendar/v3/calendars/*/events*' => Http::response([
                'items' => [
                    [
                        'id' => 'remote_event_001',
                        'summary' => 'Jadwal Dari Google Calendar',
                        'description' => 'Event diinput via aplikasi mobile Google Calendar',
                        'etag' => '"etag_remote_001"',
                        'htmlLink' => 'https://calendar.google.com/event?eid=001',
                        'start' => ['dateTime' => now()->addDays(3)->toRfc3339String()],
                        'end' => ['dateTime' => now()->addDays(3)->addHour()->toRfc3339String()],
                    ],
                ],
            ], 200),
        ]);

        $account = GoogleCalendarAccount::create([
            'business_id' => $this->business->id,
            'google_id' => 'google_user_998877',
            'email' => 'merchant@gmail.com',
            'access_token' => 'valid_token_abc',
            'token_expires_at' => now()->addHour(),
            'is_active' => true,
        ]);

        $syncService = app(GoogleCalendarSyncService::class);
        $count = $syncService->pullEvents($account);

        $this->assertEquals(1, $count);
        $this->assertDatabaseHas('calendar_events', [
            'business_id' => $this->business->id,
            'google_event_id' => 'remote_event_001',
            'title' => 'Jadwal Dari Google Calendar',
            'sync_status' => 'synced',
        ]);

        // Pull again with unchanged etag (should skip without duplicates)
        $countSecond = $syncService->pullEvents($account);
        $this->assertEquals(0, $countSecond);
        $this->assertEquals(1, CalendarEvent::where('google_event_id', 'remote_event_001')->count());
    }

    public function test_can_disconnect_google_calendar(): void
    {
        $account = GoogleCalendarAccount::create([
            'business_id' => $this->business->id,
            'google_id' => 'google_user_998877',
            'email' => 'merchant@gmail.com',
            'access_token' => 'valid_token_abc',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('operations.calendar.google.disconnect'));

        $response->assertRedirect(route('operations.calendar.settings'));
        $this->assertFalse((bool) $account->fresh()->is_active);
    }

    public function test_multiple_users_can_connect_their_own_personal_google_calendar_accounts(): void
    {
        // Create secondary manager user in same business
        $staff = User::create([
            'name' => 'Staf Operasional',
            'email' => 'staff@testoperations.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->business->users()->attach($staff->id, [
            'id' => (string) Str::uuid(),
            'role' => 'manager',
            'is_active' => true,
        ]);
        $staff->update(['active_business_id' => $this->business->id]);

        // Account 1: Owner
        $ownerAccount = GoogleCalendarAccount::create([
            'business_id' => $this->business->id,
            'user_id' => $this->owner->id,
            'google_id' => 'owner_google_123',
            'email' => 'owner@example.com',
            'access_token' => 'token_owner_abc',
            'is_active' => true,
        ]);

        // Account 2: Staff
        $staffAccount = GoogleCalendarAccount::create([
            'business_id' => $this->business->id,
            'user_id' => $staff->id,
            'google_id' => 'staff_google_456',
            'email' => 'staff@example.com',
            'access_token' => 'token_staff_xyz',
            'is_active' => true,
        ]);

        // Assert both coexist in the same business
        $this->assertEquals(2, GoogleCalendarAccount::where('business_id', $this->business->id)->count());

        // When staff visits calendar settings, their personal account is retrieved
        $response = $this->actingAs($staff)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('operations.calendar.settings'));

        $response->assertOk()
            ->assertViewHas('myGoogleAccount', fn($acc) => $acc && $acc->id === $staffAccount->id);
    }

    public function test_task_with_sync_to_calendar_automatically_creates_linked_calendar_event(): void
    {
        $dueDate = now()->addDays(2)->setHour(14)->setMinute(0);

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('operations.tasks.store'), [
                'title' => 'Deadline Review Laporan Pajak',
                'due_date' => $dueDate->toDateTimeString(),
                'sync_to_calendar' => true,
                'priority' => 'high',
            ]);

        $task = Task::where('business_id', $this->business->id)->where('title', 'Deadline Review Laporan Pajak')->first();
        $this->assertNotNull($task);

        // Verify linked CalendarEvent was automatically generated
        $linkedEvent = CalendarEvent::where('task_id', $task->id)->first();
        $this->assertNotNull($linkedEvent);
        $this->assertStringContainsString('Deadline Review Laporan Pajak', $linkedEvent->title);
        $this->assertEquals($this->business->id, $linkedEvent->business_id);

        // Toggle task completion and verify calendar event status is updated
        $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('operations.tasks.toggle', $task));

        $this->assertEquals('completed', $linkedEvent->fresh()->status);
    }
}
