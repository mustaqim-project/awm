<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Location;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\AdminRbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminB2bTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private Customer $customer;
    private Unit $unit;
    private Product $product;
    private Location $location;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(AdminRbacSeeder::class);

        $this->user = User::create([
            'name'     => 'Owner Bisnis',
            'email'    => 'owner_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->business = Business::create([
            'name'                => 'PT Solusi Distribusi Nusantara',
            'address'             => 'Jl. Jenderal Sudirman Kav 21, Jakarta Selatan',
            'phone'               => '081234567890',
            'email'               => 'finance@solusidistribusi.id',
            'currency'            => 'IDR',
            'bank_name'           => 'BCA',
            'bank_account_number' => '8040-9921-88',
            'bank_account_holder' => 'PT Solusi Distribusi Nusantara',
        ]);

        $this->business->users()->attach($this->user->id, [
            'id'        => (string) Str::uuid(),
            'role'      => 'owner',
            'is_active' => true,
        ]);

        $this->unit = Unit::create([
            'business_id' => $this->business->id,
            'code'        => 'PCS',
            'name'        => 'Pieces',
            'category'    => Unit::CATEGORY_QUANTITY,
            'is_base'     => true,
        ]);

        $this->product = Product::create([
            'business_id'    => $this->business->id,
            'name'           => 'Cooca POS Terminal Pro',
            'code'           => 'HW-POS-01',
            'type'           => Product::TYPE_GOODS,
            'selling_price'  => 2500000,
            'output_unit_id' => $this->unit->id,
            'is_active'      => true,
        ]);

        $this->customer = Customer::create([
            'business_id'  => $this->business->id,
            'name'         => 'Budi Hartono',
            'company_name' => 'PT Ritel Modern Selaras',
            'phone'        => '081299988877',
            'email'        => 'procurement@ritelmodern.id',
            'address'      => 'Kawasan Niaga Kelapa Gading Blok C-12, Jakarta Utara',
        ]);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name'        => 'Gudang Pusat Jakarta',
            'code'        => 'WH-JKT',
            'is_active'   => true,
        ]);
    }

    private function createAdmin(string $role): Admin
    {
        return Admin::factory()->create([
            'name'      => "Admin {$role}",
            'email'     => "{$role}_" . uniqid() . "@example.com",
            'password'  => Hash::make('password123'),
            'role'      => $role,
            'is_active' => true,
        ]);
    }

    public function test_super_admin_and_finance_admin_can_access_b2b_hub_and_all_tabs(): void
    {
        $superAdmin   = $this->createAdmin('super_admin');
        $financeAdmin = $this->createAdmin('finance_admin');
        $supportAdmin = $this->createAdmin('support_admin');

        // 1. Tab Quotations
        $this->actingAs($superAdmin, 'admin')
            ->get(route('admin.b2b.index', ['tab' => 'quotations']))
            ->assertStatus(200)
            ->assertSee('Penjualan B2B &amp; Faktur Komersial', false)
            ->assertSee('Daftar Surat Penawaran Harga', false);

        // 2. Tab Orders
        $this->actingAs($financeAdmin, 'admin')
            ->get(route('admin.b2b.index', ['tab' => 'orders']))
            ->assertStatus(200)
            ->assertSee('Daftar Pesanan Penjualan (Sales Orders / SO)', false);

        // 3. Tab Invoices
        $this->actingAs($financeAdmin, 'admin')
            ->get(route('admin.b2b.index', ['tab' => 'invoices']))
            ->assertStatus(200)
            ->assertSee('Daftar Faktur Penjualan Komersial (Invoices &amp; AR)', false);

        // 4. Tab Returns
        $this->actingAs($financeAdmin, 'admin')
            ->get(route('admin.b2b.index', ['tab' => 'returns']))
            ->assertStatus(200)
            ->assertSee('Daftar Retur Penjualan &amp; Nota Kredit', false);

        // 5. Support Admin forbidden (no admin.b2b.view permission)
        $this->actingAs($supportAdmin, 'admin')
            ->get(route('admin.b2b.index'))
            ->assertStatus(403);
    }

    public function test_can_download_official_pdf_for_all_four_document_types(): void
    {
        $financeAdmin = $this->createAdmin('finance_admin');

        // 1. Create Quotation & Item
        $quotation = Quotation::create([
            'business_id'      => $this->business->id,
            'customer_id'      => $this->customer->id,
            'quotation_number' => 'Q-ADM-202610-001',
            'date'             => '2026-10-20',
            'expiry_date'      => '2026-11-20',
            'subtotal'         => 5000000,
            'total_amount'     => 5550000,
            'tax_amount'       => 550000,
            'status'           => Quotation::STATUS_SENT,
            'created_by'       => $this->user->id,
        ]);
        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'product_id'   => $this->product->id,
            'product_name' => $this->product->name,
            'unit_price'   => 2500000,
            'quantity'     => 2,
            'subtotal'     => 5000000,
        ]);

        // Download Quotation PDF
        $resQ = $this->actingAs($financeAdmin, 'admin')
            ->get(route('admin.b2b.quotations.pdf', $quotation));
        $resQ->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $resQ->headers->get('Content-Type') ?? '');

        // 2. Create Sales Order & Item
        $salesOrder = SalesOrder::create([
            'business_id' => $this->business->id,
            'customer_id' => $this->customer->id,
            'quotation_id' => $quotation->id,
            'so_number'   => 'SO-ADM-202610-001',
            'order_date'  => '2026-10-21',
            'subtotal'    => 5000000,
            'total_amount'=> 5550000,
            'tax_amount'  => 550000,
            'status'      => SalesOrder::STATUS_CONFIRMED,
            'created_by'  => $this->user->id,
        ]);
        SalesOrderItem::create([
            'sales_order_id' => $salesOrder->id,
            'product_id'     => $this->product->id,
            'product_name'   => $this->product->name,
            'unit_price'     => 2500000,
            'quantity'       => 2,
            'subtotal'       => 5000000,
        ]);

        // Download Sales Order PDF
        $resSO = $this->actingAs($financeAdmin, 'admin')
            ->get(route('admin.b2b.orders.pdf', $salesOrder));
        $resSO->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $resSO->headers->get('Content-Type') ?? '');

        // 3. Create Invoice & Item
        $invoice = Invoice::create([
            'business_id'    => $this->business->id,
            'customer_id'    => $this->customer->id,
            'sales_order_id' => $salesOrder->id,
            'invoice_number' => 'INV-ADM-202610-001',
            'invoice_date'   => '2026-10-22',
            'due_date'       => '2026-11-05',
            'subtotal'       => 5000000,
            'tax_amount'     => 550000,
            'total_amount'   => 5550000,
            'paid_amount'    => 0,
            'balance_due'    => 5550000,
            'status'         => Invoice::STATUS_UNPAID,
            'created_by'     => $this->user->id,
        ]);
        InvoiceItem::create([
            'invoice_id'   => $invoice->id,
            'product_id'   => $this->product->id,
            'unit_id'      => $this->unit->id,
            'item_name'    => $this->product->name,
            'unit_price'   => 2500000,
            'quantity'     => 2,
            'subtotal'     => 5000000,
        ]);

        // Download Invoice PDF
        $resInv = $this->actingAs($financeAdmin, 'admin')
            ->get(route('admin.b2b.invoices.pdf', $invoice));
        $resInv->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $resInv->headers->get('Content-Type') ?? '');

        // 4. Create Sales Return & Item
        $salesReturn = SalesReturn::create([
            'business_id'    => $this->business->id,
            'customer_id'    => $this->customer->id,
            'invoice_id'     => $invoice->id,
            'location_id'    => $this->location->id,
            'return_number'  => 'RET-ADM-202610-001',
            'return_date'    => '2026-10-25',
            'reason'         => 'Layar unit retak saat pengiriman',
            'total_amount'   => 2500000,
            'status'         => SalesReturn::STATUS_APPROVED,
            'refund_method'  => SalesReturn::REFUND_CREDIT_NOTE,
            'created_by'     => $this->user->id,
        ]);
        SalesReturnItem::create([
            'sales_return_id' => $salesReturn->id,
            'product_id'      => $this->product->id,
            'item_name'       => $this->product->name,
            'quantity'        => 1,
            'unit_price'      => 2500000,
            'subtotal'        => 2500000,
            'reason'          => 'Kerusakan pabrik',
        ]);

        // Download Sales Return PDF
        $resRet = $this->actingAs($financeAdmin, 'admin')
            ->get(route('admin.b2b.returns.pdf', $salesReturn));
        $resRet->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $resRet->headers->get('Content-Type') ?? '');
    }

    public function test_can_convert_quotation_and_generate_invoice_and_record_payment(): void
    {
        $financeAdmin = $this->createAdmin('finance_admin');

        // 1. Create Quotation
        $quotation = Quotation::create([
            'business_id'      => $this->business->id,
            'customer_id'      => $this->customer->id,
            'quotation_number' => 'Q-CONV-001',
            'date'             => '2026-10-20',
            'subtotal'         => 2500000,
            'total_amount'     => 2775000,
            'tax_amount'       => 275000,
            'status'           => Quotation::STATUS_ACCEPTED,
            'created_by'       => $this->user->id,
        ]);
        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'product_id'   => $this->product->id,
            'product_name' => $this->product->name,
            'unit_price'   => 2500000,
            'quantity'     => 1,
            'subtotal'     => 2500000,
        ]);

        // Convert to Sales Order
        $resConvert = $this->actingAs($financeAdmin, 'admin')
            ->post(route('admin.b2b.quotations.convert', $quotation));

        $resConvert->assertRedirect();
        $this->assertDatabaseHas('sales_orders', [
            'quotation_id' => $quotation->id,
            'business_id'  => $this->business->id,
        ]);

        $salesOrder = SalesOrder::where('quotation_id', $quotation->id)->firstOrFail();

        // 2. Generate Invoice from Sales Order
        $resInvoice = $this->actingAs($financeAdmin, 'admin')
            ->post(route('admin.b2b.orders.generate-invoice', $salesOrder));

        $resInvoice->assertRedirect();
        $this->assertDatabaseHas('invoices', [
            'sales_order_id' => $salesOrder->id,
            'business_id'    => $this->business->id,
        ]);

        $invoice = Invoice::where('sales_order_id', $salesOrder->id)->firstOrFail();
        $this->assertEquals(2775000.00, (float) $invoice->total_amount);
        $this->assertEquals(2775000.00, (float) $invoice->balance_due);

        // 3. Record Payment on Invoice
        $resPay = $this->actingAs($financeAdmin, 'admin')
            ->post(route('admin.b2b.invoices.payments.store', $invoice), [
                'amount'           => 2775000,
                'payment_date'     => '2026-10-25',
                'payment_method'   => 'bank_transfer',
                'reference_number' => 'TRF-PAY-009',
                'notes'            => 'Pelunasan penuh B2B via admin',
            ]);

        $resPay->assertRedirect();
        $invoice->refresh();
        $this->assertEquals(2775000.00, (float) $invoice->paid_amount);
        $this->assertEquals(0.00, (float) $invoice->balance_due);
        $this->assertEquals(Invoice::STATUS_PAID, $invoice->status);
    }

    public function test_can_export_b2b_datasets_to_csv(): void
    {
        $financeAdmin = $this->createAdmin('finance_admin');

        // Export Quotations
        $resQ = $this->actingAs($financeAdmin, 'admin')
            ->get(route('admin.b2b.export', ['tab' => 'quotations']));
        $resQ->assertStatus(200);
        $this->assertStringContainsString('text/csv', $resQ->headers->get('Content-Type') ?? '');

        // Export Orders
        $resSO = $this->actingAs($financeAdmin, 'admin')
            ->get(route('admin.b2b.export', ['tab' => 'orders']));
        $resSO->assertStatus(200);
        $this->assertStringContainsString('text/csv', $resSO->headers->get('Content-Type') ?? '');

        // Export Invoices
        $resInv = $this->actingAs($financeAdmin, 'admin')
            ->get(route('admin.b2b.export', ['tab' => 'invoices']));
        $resInv->assertStatus(200);
        $this->assertStringContainsString('text/csv', $resInv->headers->get('Content-Type') ?? '');

        // Export Returns
        $resRet = $this->actingAs($financeAdmin, 'admin')
            ->get(route('admin.b2b.export', ['tab' => 'returns']));
        $resRet->assertStatus(200);
        $this->assertStringContainsString('text/csv', $resRet->headers->get('Content-Type') ?? '');
    }
}
