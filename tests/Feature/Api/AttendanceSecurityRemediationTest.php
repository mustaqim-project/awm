<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Domain\HRM\Biometrics\FaceVerificationService;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\Location;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class AttendanceSecurityRemediationTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $employee;
    private Location $officeLocation;
    private FaceVerificationService $faceService;
    private string $validFaceSample = 'employee-verified-biometric-face-sample';

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->faceService = app(FaceVerificationService::class);

        $this->business = Business::create([
            'name' => 'PT Cooca Solusi Digital',
            'slug' => 'cooca-solusi-digital-sec',
            'email' => 'info@cooca-security-test.com',
            'is_active' => true,
        ]);

        $this->employee = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@cooca-security-test.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'phone' => '081234567890',
            'active_business_id' => $this->business->id,
        ]);

        $this->business->users()->attach($this->employee->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
            'attendance_mode' => 'geofenced',
            'is_active' => true,
        ]);

        $this->officeLocation = Location::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Kantor Pusat Jakarta',
            'address' => 'Jl. Medan Merdeka Barat No. 1, Jakarta',
            'latitude' => -6.175392,
            'longitude' => 106.827153,
            'geofence_radius_meters' => 50,
            'is_primary' => true,
            'is_active' => true,
        ]);

        // Register biometric face template for attendance authorization
        $this->faceService->registerFaceTemplate($this->business, $this->employee, $this->validFaceSample);
    }

    private function authenticateEmployee(): void
    {
        Sanctum::actingAs($this->employee);
        $membership = BusinessMembership::where('business_id', $this->business->id)
            ->where('user_id', $this->employee->id)
            ->firstOrFail();
        Context::setBusiness($this->business, $membership);
    }

    /**
     * 1. Test Clock-in is strictly rejected when is_mock is true.
     */
    public function test_clock_in_rejected_when_is_mock_is_true(): void
    {
        Carbon::setTestNow('2026-11-03 08:00:00');
        $this->authenticateEmployee();

        $response = $this->postJson('/api/v1/attendance/check-in', [
            'latitude' => -6.175392,
            'longitude' => 106.827153,
            'accuracy' => 10.0,
            'location_id' => $this->officeLocation->id,
            'face_data' => $this->validFaceSample,
            'is_mock' => true,
            'device_id' => 'fake-gps-device-99',
            'is_dev_mode' => true,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['gps']);
        $this->assertStringContainsString('Mock Location / Fake GPS', $response->json('message') ?? '');

        // Verify zero attendances recorded
        $this->assertDatabaseMissing('attendances', [
            'business_id' => $this->business->id,
            'user_id' => $this->employee->id,
        ]);

        Carbon::setTestNow();
    }

    /**
     * 2. Test Clock-out is strictly rejected when is_mock is true.
     */
    public function test_clock_out_rejected_when_is_mock_is_true(): void
    {
        Carbon::setTestNow('2026-11-03 08:00:00');
        $this->authenticateEmployee();

        // Legitimate Clock-in
        $inResponse = $this->postJson('/api/v1/attendance/check-in', [
            'latitude' => -6.175392,
            'longitude' => 106.827153,
            'accuracy' => 10.0,
            'location_id' => $this->officeLocation->id,
            'face_data' => $this->validFaceSample,
            'is_mock' => false,
            'device_id' => 'device-genuine-1',
        ]);
        $inResponse->assertOk();

        // Clock-out attempt with Fake GPS enabled
        Carbon::setTestNow('2026-11-03 17:00:00');
        $outResponse = $this->postJson('/api/v1/attendance/check-out', [
            'latitude' => -6.175392,
            'longitude' => 106.827153,
            'accuracy' => 10.0,
            'face_data' => $this->validFaceSample,
            'is_mock' => true,
            'device_id' => 'device-genuine-1',
        ]);

        $outResponse->assertStatus(422);
        $outResponse->assertJsonValidationErrors(['gps']);
        $this->assertStringContainsString('Mock Location / Fake GPS', $outResponse->json('message') ?? '');

        // Verify clock_out_at was not recorded
        $attendance = Attendance::where('business_id', $this->business->id)
            ->where('user_id', $this->employee->id)
            ->firstOrFail();
        $this->assertNull($attendance->clock_out_at);

        Carbon::setTestNow();
    }

    /**
     * 3. Test Risk Policy Level 1: Developer Options / ADB enabled without mock location is allowed.
     */
    public function test_clock_in_allowed_with_developer_options_under_risk_proportionate_policy(): void
    {
        Carbon::setTestNow('2026-11-03 08:00:00');
        $this->authenticateEmployee();

        $response = $this->postJson('/api/v1/attendance/check-in', [
            'latitude' => -6.175392,
            'longitude' => 106.827153,
            'accuracy' => 15.0,
            'location_id' => $this->officeLocation->id,
            'face_data' => $this->validFaceSample,
            'is_mock' => false,
            'is_dev_mode' => true,
            'is_rooted' => false,
            'device_id' => 'dev-pixel-9-pro',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data' => [
                'is_mock' => false,
                'is_dev_mode' => true,
                'device_id' => 'dev-pixel-9-pro',
            ],
        ]);

        // Verify database entry
        $this->assertDatabaseHas('attendances', [
            'business_id' => $this->business->id,
            'user_id' => $this->employee->id,
            'is_mock' => 0,
            'is_dev_mode' => 1,
            'device_id' => 'dev-pixel-9-pro',
        ]);

        // Verify Audit Log captured elevated risk monitoring
        $log = AuditLog::where('business_id', $this->business->id)
            ->where('user_id', $this->employee->id)
            ->where('action', 'attendance.clock_in')
            ->firstOrFail();

        $this->assertEquals(AuditLog::RISK_MEDIUM, $log->risk_level);
        $this->assertStringContainsString('Mode Pengembang', $log->risk_reason);
        $this->assertEquals('dev-pixel-9-pro', $log->new_values['device_id']);
        $this->assertTrue($log->new_values['is_dev_mode']);

        Carbon::setTestNow();
    }

    /**
     * 4. Test security telemetry persistence in attendances table.
     */
    public function test_security_telemetry_persisted_in_database(): void
    {
        Carbon::setTestNow('2026-11-03 08:00:00');
        $this->authenticateEmployee();

        $response = $this->postJson('/api/v1/attendance/check-in', [
            'latitude' => -6.175392,
            'longitude' => 106.827153,
            'accuracy' => 12.5,
            'location_id' => $this->officeLocation->id,
            'face_data' => $this->validFaceSample,
            'is_mock' => false,
            'is_dev_mode' => false,
            'is_rooted' => true,
            'device_id' => 'samsung-hw-root-77',
            'security_flags' => [
                'battery_level' => 88,
                'wifi_ssid' => 'Office_Mesh_5G',
            ],
        ]);

        $response->assertOk();

        $attendance = Attendance::where('business_id', $this->business->id)
            ->where('user_id', $this->employee->id)
            ->firstOrFail();

        $this->assertEquals('samsung-hw-root-77', $attendance->device_id);
        $this->assertFalse($attendance->is_mock);
        $this->assertFalse($attendance->is_dev_mode);
        $this->assertTrue($attendance->is_rooted);
        $this->assertEquals(12.5, $attendance->accuracy);
        $this->assertEquals(88, $attendance->security_flags['battery_level']);
        $this->assertEquals('Office_Mesh_5G', $attendance->security_flags['wifi_ssid']);

        Carbon::setTestNow();
    }

    /**
     * 5. Test Play Integrity evaluation degrades gracefully when cloud credentials are unconfigured.
     */
    public function test_play_integrity_token_evaluation_graceful_degradation(): void
    {
        Carbon::setTestNow('2026-11-03 08:00:00');
        $this->authenticateEmployee();

        $mockToken = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.dummy_play_integrity_token';

        $response = $this->postJson('/api/v1/attendance/check-in', [
            'latitude' => -6.175392,
            'longitude' => 106.827153,
            'accuracy' => 8.0,
            'location_id' => $this->officeLocation->id,
            'face_data' => $this->validFaceSample,
            'is_mock' => false,
            'device_id' => 'play-integrity-tested-dev',
            'play_integrity_token' => $mockToken,
        ]);

        $response->assertOk();

        $attendance = Attendance::where('business_id', $this->business->id)
            ->where('user_id', $this->employee->id)
            ->firstOrFail();

        $this->assertEquals('degraded_local_integrity_verified', $attendance->security_flags['play_integrity_status']);

        Carbon::setTestNow();
    }

    /**
     * 6. Test Clock-in is rejected when GPS accuracy exceeds threshold (>250m).
     */
    public function test_clock_in_rejected_when_gps_accuracy_exceeds_threshold(): void
    {
        Carbon::setTestNow('2026-11-03 08:00:00');
        $this->authenticateEmployee();

        $response = $this->postJson('/api/v1/attendance/check-in', [
            'latitude' => -6.175392,
            'longitude' => 106.827153,
            'accuracy' => 350.0,
            'location_id' => $this->officeLocation->id,
            'face_data' => $this->validFaceSample,
            'is_mock' => false,
            'device_id' => 'low-accuracy-dev',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['gps']);
        $this->assertStringContainsString('Akurasi sinyal GPS perangkat Anda terlalu rendah', $response->json('message') ?? '');

        Carbon::setTestNow();
    }
}
