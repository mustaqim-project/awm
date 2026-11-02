<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Domain\Storage\TenantStorage;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\Expense;
use App\Models\Location;
use App\Models\PosCashMovement;
use App\Models\PosShift;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class PosExpenseApiTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $cashier;
    private User $unauthorizedUser;
    private Location $location;
    private PosShift $openShift;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        Storage::fake(TenantStorage::DISK_PUBLIC);

        // 1. Business
        $this->business = Business::create([
            'id' => (string) Str::uuid(),
            'name' => 'Kopi Mantap Jiwa',
            'slug' => 'kopi-mantap-jiwa',
            'is_active' => true,
        ]);

        // 2. Location
        $this->location = Location::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Outlet Utama',
            'is_primary' => true,
            'is_active' => true,
        ]);

        // 3. Cashier User with pos.terminal permission (role: owner or cashier)
        $this->cashier = User::create([
            'name' => 'Budi Kasir',
            'email' => 'budi@kopimantap.com',
            'password' => bcrypt('password123'),
        ]);

        $this->business->users()->attach($this->cashier->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner', // Owner has bypass for all permissions on enabled modules
            'is_active' => true,
        ]);
        $this->cashier->update(['active_business_id' => $this->business->id]);

        // 4. Unauthorized User (different business / no permissions)
        $otherBusiness = Business::create([
            'id' => (string) Str::uuid(),
            'name' => 'Bisnis Lain',
            'slug' => 'bisnis-lain',
            'is_active' => true,
        ]);
        $this->unauthorizedUser = User::create([
            'name' => 'Orang Asing',
            'email' => 'asing@example.com',
            'password' => bcrypt('password123'),
        ]);
        $otherBusiness->users()->attach($this->unauthorizedUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
            'is_active' => true,
        ]);
        $this->unauthorizedUser->update(['active_business_id' => $otherBusiness->id]);

        // 5. Active Open Shift
        $this->openShift = PosShift::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'opened_at' => now(),
            'opening_cash' => 200000.0,
            'total_cash_out' => 0.0,
            'status' => PosShift::STATUS_OPEN,
        ]);
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->postJson('/api/v1/pos/expenses', [
            'pos_shift_id' => $this->openShift->id,
            'amount' => 25000,
            'category' => 'supplies',
            'notes' => 'Beli es batu',
        ]);

        $response->assertStatus(401);
    }

    public function test_cannot_record_expense_on_other_business_shift(): void
    {
        Sanctum::actingAs($this->unauthorizedUser);

        $response = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->postJson('/api/v1/pos/expenses', [
            'pos_shift_id' => $this->openShift->id,
            'amount' => 25000,
            'category' => 'supplies',
            'notes' => 'Beli es batu',
        ]);

        // Unauthorized user does not belong to $this->business with proper permission
        $this->assertContains($response->status(), [403, 404, 409]);
    }

    public function test_validation_fails_for_invalid_payload(): void
    {
        Sanctum::actingAs($this->cashier);

        $response = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->postJson('/api/v1/pos/expenses', [
            'pos_shift_id' => $this->openShift->id,
            'amount' => 0, // Must be min: 1
            'category' => '',
            'notes' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount', 'category', 'notes']);
    }

    public function test_cannot_record_expense_on_closed_shift(): void
    {
        $closedShift = PosShift::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'opened_at' => now()->subHours(8),
            'closed_at' => now()->subHour(),
            'opening_cash' => 200000.0,
            'status' => PosShift::STATUS_CLOSED,
        ]);

        Sanctum::actingAs($this->cashier);

        $response = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->postJson('/api/v1/pos/expenses', [
            'pos_shift_id' => $closedShift->id,
            'amount' => 35000,
            'category' => 'operational',
            'notes' => 'Beli plastik sampah',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Shift kasir telah ditutup atau tidak aktif.',
            ]);
    }

    public function test_cannot_record_expense_on_non_existent_shift(): void
    {
        Sanctum::actingAs($this->cashier);

        $response = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->postJson('/api/v1/pos/expenses', [
            'pos_shift_id' => (string) Str::uuid(),
            'amount' => 35000,
            'category' => 'operational',
            'notes' => 'Beli galon air',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Shift kasir telah ditutup atau tidak aktif.',
            ]);
    }

    public function test_cashier_can_record_valid_expense_with_file_upload(): void
    {
        Sanctum::actingAs($this->cashier);

        $file = UploadedFile::fake()->image('receipt_nota.jpg', 600, 800);

        $response = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->post('/api/v1/pos/expenses', [
            'pos_shift_id' => $this->openShift->id,
            'amount' => 25000,
            'category' => 'supplies',
            'notes' => 'Beli es batu kristal 2 bal',
            'receipt_image' => $file,
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Pengeluaran kas kecil berhasil dicatat.',
                'data' => [
                    'pos_shift_id' => $this->openShift->id,
                    'amount' => 25000,
                    'category' => 'supplies',
                    'notes' => 'Beli es batu kristal 2 bal',
                ],
            ]);

        $json = $response->json();
        $this->assertNotEmpty($json['data']['id']);
        $this->assertNotEmpty($json['data']['receipt_url']);
        $this->assertNotEmpty($json['data']['cash_movement_id']);
        $this->assertNotEmpty($json['data']['cash_transaction_id']);

        // Verify PosCashMovement created
        $this->assertDatabaseHas('pos_cash_movements', [
            'id' => $json['data']['cash_movement_id'],
            'business_id' => $this->business->id,
            'pos_shift_id' => $this->openShift->id,
            'type' => PosCashMovement::TYPE_CASH_OUT,
            'amount' => 25000,
        ]);

        // Verify CashTransaction recorded in CashLedger
        $this->assertDatabaseHas('cash_transactions', [
            'id' => $json['data']['cash_transaction_id'],
            'business_id' => $this->business->id,
            'type' => CashTransaction::TYPE_OUT,
            'reference_type' => 'pos_shift_expense',
            'amount' => 25000,
        ]);

        // Verify Expense model created
        $this->assertDatabaseHas('expenses', [
            'id' => $json['data']['id'],
            'business_id' => $this->business->id,
            'category' => 'supplies',
            'amount' => 25000,
            'payment_method' => 'cash',
        ]);

        // Verify shift total_cash_out incremented
        $this->assertSame(25000.0, (float) $this->openShift->fresh()->total_cash_out);
    }

    public function test_cashier_can_record_valid_expense_with_base64_image(): void
    {
        Sanctum::actingAs($this->cashier);

        $fakeBase64 = 'data:image/jpeg;base64,' . base64_encode('fake-image-binary-data');

        $response = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->postJson('/api/v1/pos/expenses', [
            'pos_shift_id' => $this->openShift->id,
            'amount' => 15000,
            'category' => 'operational',
            'notes' => 'Beli sabun cuci piring',
            'receipt_image_base64' => $fakeBase64,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Pengeluaran kas kecil berhasil dicatat.',
                'data' => [
                    'amount' => 15000,
                    'category' => 'operational',
                    'notes' => 'Beli sabun cuci piring',
                ],
            ]);

        $json = $response->json();
        $this->assertNotNull($json['data']['receipt_url']);
        $this->assertStringContainsString('expenses', $json['data']['receipt_url']);
    }

    public function test_multiple_expenses_accumulate_shift_total_cash_out(): void
    {
        Sanctum::actingAs($this->cashier);

        // Expense 1: Rp 10.000
        $this->withHeaders(['X-Business-Id' => $this->business->id])
            ->postJson('/api/v1/pos/expenses', [
                'pos_shift_id' => $this->openShift->id,
                'amount' => 10000,
                'category' => 'transport',
                'notes' => 'Ongkir titip beli barang',
            ])->assertStatus(201);

        // Expense 2: Rp 20.000
        $this->withHeaders(['X-Business-Id' => $this->business->id])
            ->postJson('/api/v1/pos/expenses', [
                'pos_shift_id' => $this->openShift->id,
                'amount' => 20000,
                'category' => 'supplies',
                'notes' => 'Beli gula pasir 1 kg',
            ])->assertStatus(201);

        // Total cash out should equal 10.000 + 20.000 = 30.000
        $this->assertSame(30000.0, (float) $this->openShift->fresh()->total_cash_out);
        $this->assertSame(2, PosCashMovement::where('pos_shift_id', $this->openShift->id)->count());
        $this->assertSame(2, CashTransaction::where('business_id', $this->business->id)->where('reference_type', 'pos_shift_expense')->count());
    }
}
