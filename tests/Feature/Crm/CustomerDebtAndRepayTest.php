<?php

declare(strict_types=1);

namespace Tests\Feature\Crm;

use App\Models\AuditLog;
use App\Models\Business;
use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\CustomerCreditTransaction;
use App\Models\Location;
use App\Models\PosCashMovement;
use App\Models\PosOrder;
use App\Models\PosOrderPayment;
use App\Models\PosShift;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class CustomerDebtAndRepayTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $cashier;
    private Location $location;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        // 1. Setup Tenant Business
        $this->business = Business::create([
            'id' => (string) Str::uuid(),
            'name' => 'Toko Sembako Berkah',
            'slug' => 'toko-sembako-berkah',
            'is_active' => true,
        ]);

        // 2. Location
        $this->location = Location::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Toko Pusat',
            'is_primary' => true,
            'is_active' => true,
        ]);

        // 3. User with Owner role (all permissions enabled)
        $this->cashier = User::create([
            'name' => 'Ahmad Kasir',
            'email' => 'ahmad@sembakoberkah.com',
            'password' => bcrypt('secret123'),
        ]);

        $this->business->users()->attach($this->cashier->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);
        $this->cashier->update(['active_business_id' => $this->business->id]);

        // 4. Customer with initial credit balance
        $this->customer = Customer::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Pak Haji Ridwan',
            'phone' => '081234567890',
            'company_name' => 'Warung Berkah',
            'credit_limit' => 5000000.0,
            'current_credit_balance' => 750000.0,
            'payment_terms_days' => 30,
            'is_active' => true,
        ]);
    }

    public function test_unauthenticated_requests_are_rejected(): void
    {
        $resGet = $this->getJson("/api/v1/customers/{$this->customer->id}/debts");
        $resGet->assertStatus(401);

        $resPost = $this->postJson("/api/v1/customers/{$this->customer->id}/repay-debt", [
            'amount' => 100000,
        ]);
        $resPost->assertStatus(401);
    }

    public function test_can_get_customer_debts_overview_and_transactions(): void
    {
        Sanctum::actingAs($this->cashier);

        // Record a previous charge transaction
        CustomerCreditTransaction::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'customer_id' => $this->customer->id,
            'type' => CustomerCreditTransaction::TYPE_CHARGE,
            'amount' => 750000.0,
            'balance_after' => 750000.0,
            'reference_type' => 'pos_order',
            'reference_id' => (string) Str::uuid(),
            'notes' => 'Belanja POS tempo (kredit) #ORD-20261028-045',
            'created_by' => $this->cashier->id,
        ]);

        // Create an unpaid POS order with customer_credit payment
        $order = PosOrder::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'customer_id' => $this->customer->id,
            'order_number' => 'ORD-20261028-045',
            'order_date' => now(),
            'status' => PosOrder::STATUS_COMPLETED,
            'total_amount' => 750000.0,
            'paid_amount' => 750000.0,
        ]);

        PosOrderPayment::create([
            'id' => (string) Str::uuid(),
            'pos_order_id' => $order->id,
            'payment_method' => PosOrderPayment::METHOD_CUSTOMER_CREDIT,
            'amount' => 750000.0,
            'status' => 'completed',
        ]);

        $response = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->getJson("/api/v1/customers/{$this->customer->id}/debts");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'customer' => [
                'id' => $this->customer->id,
                'name' => 'Pak Haji Ridwan',
                'credit_limit' => 5000000.0,
                'current_credit_balance' => 750000.0,
                'available_credit' => 4250000.0,
                'payment_terms_days' => 30,
            ],
        ]);

        $json = $response->json();
        $this->assertCount(1, $json['transactions']);
        $this->assertEquals(750000.0, $json['transactions'][0]['amount']);
        $this->assertEquals('charge', $json['transactions'][0]['type']);
        $this->assertCount(1, $json['unpaid_orders']);
        $this->assertEquals('ORD-20261028-045', $json['unpaid_orders'][0]['order_number']);
    }

    public function test_can_get_customer_debts_via_crm_prefix_alias(): void
    {
        Sanctum::actingAs($this->cashier);

        $response = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->getJson("/api/v1/crm/customers/{$this->customer->id}/debts");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'customer' => [
                'id' => $this->customer->id,
                'current_credit_balance' => 750000.0,
            ],
        ]);
    }

    public function test_cannot_access_customer_debt_of_another_business(): void
    {
        Sanctum::actingAs($this->cashier);

        $otherBiz = Business::create([
            'id' => (string) Str::uuid(),
            'name' => 'Bisnis Tetangga',
            'slug' => 'bisnis-tetangga',
            'is_active' => true,
        ]);

        $otherCustomer = Customer::create([
            'id' => (string) Str::uuid(),
            'business_id' => $otherBiz->id,
            'name' => 'Customer Tetangga',
            'phone' => '0899999999',
            'current_credit_balance' => 500000.0,
            'is_active' => true,
        ]);

        $response = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->getJson("/api/v1/customers/{$otherCustomer->id}/debts");

        $response->assertStatus(404);
    }

    public function test_can_repay_customer_debt_partially_and_updates_balances(): void
    {
        Sanctum::actingAs($this->cashier);

        $response = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->postJson("/api/v1/customers/{$this->customer->id}/repay-debt", [
            'amount' => 250000.0,
            'payment_method' => 'cash',
            'notes' => 'Cicilan bon #1 via kasir',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Pembayaran piutang berhasil dicatat dan dibukukan ke buku kas.',
            'data' => [
                'amount_paid' => 250000.0,
                'previous_balance' => 750000.0,
                'remaining_balance' => 500000.0,
                'customer' => [
                    'id' => $this->customer->id,
                    'current_credit_balance' => 500000.0,
                ],
            ],
        ]);

        // Verify Customer Balance in DB
        $this->assertEquals(500000.0, (float) $this->customer->fresh()->current_credit_balance);

        // Verify CustomerCreditTransaction created
        $this->assertDatabaseHas('customer_credit_transactions', [
            'business_id' => $this->business->id,
            'customer_id' => $this->customer->id,
            'type' => CustomerCreditTransaction::TYPE_PAYMENT,
            'amount' => 250000.0,
            'balance_after' => 500000.0,
        ]);

        // Verify CashAccount and CashTransaction created
        $cashAccount = CashAccount::where('business_id', $this->business->id)->first();
        $this->assertNotNull($cashAccount);
        $this->assertEquals(250000.0, (float) $cashAccount->current_balance);

        $this->assertDatabaseHas('cash_transactions', [
            'business_id' => $this->business->id,
            'cash_account_id' => $cashAccount->id,
            'type' => CashTransaction::TYPE_IN,
            'amount' => 250000.0,
            'reference_type' => 'customer_credit_repayment',
        ]);

        // Verify AuditLog
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'auditable_type' => Customer::class,
            'auditable_id' => $this->customer->id,
            'action' => 'customer.credit_payment_recorded',
        ]);

        // Verify WhatsApp Proof URL format
        $json = $response->json();
        $this->assertStringContainsString('https://wa.me/', $json['data']['wa_receipt_url']);
        $this->assertStringContainsString('BUKTI', rawurldecode($json['data']['wa_receipt_url']));
        $this->assertStringContainsString('500.000', rawurldecode($json['data']['wa_receipt_url']));
    }

    public function test_repay_debt_with_pos_shift_records_drawer_cash_movement(): void
    {
        Sanctum::actingAs($this->cashier);

        // Create active POS shift
        $shift = PosShift::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'opened_at' => now(),
            'opening_cash' => 100000.0,
            'total_cash_out' => 0.0,
            'status' => PosShift::STATUS_OPEN,
        ]);

        $response = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->postJson("/api/v1/customers/{$this->customer->id}/repay-debt", [
            'amount' => 150000.0,
            'payment_method' => 'cash',
            'pos_shift_id' => $shift->id,
            'notes' => 'Bayar tunai di meja kasir',
        ]);

        $response->assertStatus(200);

        // Verify Cash Movement created in the active POS shift
        $this->assertDatabaseHas('pos_cash_movements', [
            'pos_shift_id' => $shift->id,
            'type' => PosCashMovement::TYPE_CASH_IN,
            'amount' => 150000.0,
        ]);
    }

    public function test_cannot_repay_more_than_current_credit_balance(): void
    {
        Sanctum::actingAs($this->cashier);

        $response = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->postJson("/api/v1/customers/{$this->customer->id}/repay-debt", [
            'amount' => 1000000.0, // Exceeds 750,000 balance
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['amount']);
    }

    public function test_cannot_repay_when_customer_has_zero_debt(): void
    {
        Sanctum::actingAs($this->cashier);

        $this->customer->update(['current_credit_balance' => 0.0]);

        $response = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->postJson("/api/v1/customers/{$this->customer->id}/repay-debt", [
            'amount' => 50000.0,
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Pelanggan tidak memiliki sisa saldo piutang / kasbon aktif.',
        ]);
    }

    public function test_repay_debt_via_crm_prefix_alias(): void
    {
        Sanctum::actingAs($this->cashier);

        $response = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->postJson("/api/v1/crm/customers/{$this->customer->id}/repay-debt", [
            'amount' => 100000.0,
            'payment_method' => 'transfer',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'amount_paid' => 100000.0,
                'remaining_balance' => 650000.0,
            ],
        ]);
    }

    public function test_cannot_repay_debt_of_another_business_customer(): void
    {
        Sanctum::actingAs($this->cashier);

        $otherBiz = Business::create([
            'id' => (string) Str::uuid(),
            'name' => 'Bisnis Tetangga',
            'slug' => 'bisnis-tetangga',
            'is_active' => true,
        ]);

        $otherCustomer = Customer::create([
            'id' => (string) Str::uuid(),
            'business_id' => $otherBiz->id,
            'name' => 'Customer Tetangga',
            'phone' => '0899999999',
            'current_credit_balance' => 500000.0,
            'is_active' => true,
        ]);

        $response = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->postJson("/api/v1/customers/{$otherCustomer->id}/repay-debt", [
            'amount' => 100000.0,
        ]);

        $response->assertStatus(404);
    }
}
