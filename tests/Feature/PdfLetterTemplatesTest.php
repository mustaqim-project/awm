<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Location;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PdfLetterTemplatesTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private Customer $customer;
    private Unit $unit;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create([
            'name'     => 'Direktur Utama',
            'email'    => 'direktur@korporasi.test',
            'password' => bcrypt('password123'),
        ]);

        $this->business = Business::create([
            'name'                  => 'PT Industri Mandiri Nusantara',
            'address'               => 'Kawasan Industri Rancaekek Blok B3, Bandung',
            'phone'                 => '081234567890',
            'email'                 => 'kontak@industrimandiri.co.id',
            'currency'              => 'IDR',
            'bank_name'             => 'Bank Mandiri',
            'bank_account_number'   => '131-00-887766-5',
            'bank_account_holder'   => 'PT Industri Mandiri Nusantara',
        ]);

        $this->business->users()->attach($this->user->id, [
            'id'        => (string) Str::uuid(),
            'role'      => 'owner',
            'is_active' => true,
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $this->unit = Unit::create([
            'business_id' => $this->business->id,
            'code'        => 'PCS',
            'name'        => 'Pieces',
            'category'    => Unit::CATEGORY_QUANTITY,
            'is_base'     => true,
        ]);

        $this->customer = Customer::create([
            'business_id'     => $this->business->id,
            'name'            => 'Budi Santoso',
            'company_name'    => 'PT Mitra Niaga Abadi',
            'billing_address' => 'Jl. Thamrin No. 45, Jakarta Pusat',
            'phone'           => '081999888777',
        ]);

        $this->product = Product::create([
            'business_id'    => $this->business->id,
            'output_unit_id' => $this->unit->id,
            'code'           => 'PRD-001',
            'name'           => 'Produk Unggulan Resmi',
            'selling_price'  => 500000,
            'is_active'      => true,
        ]);
    }

    public function test_quotation_pdf_and_print_renders_with_official_letter_layout(): void
    {
        $quotation = Quotation::create([
            'business_id'           => $this->business->id,
            'customer_id'           => $this->customer->id,
            'quotation_number'      => 'QUO-2026-999',
            'date'                  => now()->toDateString(),
            'expiry_date'           => now()->addDays(14)->toDateString(),
            'status'                => Quotation::STATUS_DRAFT,
            'subtotal'              => 1000000,
            'total_amount'          => 1000000,
            'terms_and_conditions'  => "1. Pembayaran DP 50%.\n2. Pelunasan saat serah terima barang.",
        ]);

        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'product_id'   => $this->product->id,
            'product_name' => 'Produk Unggulan Resmi',
            'quantity'     => 2,
            'unit_price'   => 500000,
            'subtotal'     => 1000000,
        ]);

        $responsePdf = $this->actingAs($this->user)->get(route('sales.quotations.pdf', $quotation));
        $responsePdf->assertStatus(200);
        $responsePdf->assertHeader('content-type', 'application/pdf');

        $responsePrint = $this->actingAs($this->user)->get(route('sales.quotations.print', $quotation));
        $responsePrint->assertStatus(200);
        $responsePrint->assertHeader('content-type', 'application/pdf');
    }

    public function test_sales_order_pdf_and_print_renders_with_official_letter_layout(): void
    {
        $salesOrder = SalesOrder::create([
            'business_id'      => $this->business->id,
            'customer_id'      => $this->customer->id,
            'so_number'        => 'SO-2026-999',
            'order_date'       => now()->toDateString(),
            'delivery_date'    => now()->addDays(7)->toDateString(),
            'status'           => SalesOrder::STATUS_CONFIRMED,
            'shipping_address' => 'Gudang Pusat PT Mitra Niaga Abadi',
            'subtotal'         => 1500000,
            'tax_amount'       => 165000,
            'total_amount'     => 1665000,
            'created_by'       => $this->user->id,
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $salesOrder->id,
            'product_id'     => $this->product->id,
            'product_name'   => 'Produk Unggulan Resmi',
            'quantity'       => 3,
            'unit_price'     => 500000,
            'subtotal'       => 1500000,
        ]);

        $responsePdf = $this->actingAs($this->user)->get(route('sales.orders.pdf', $salesOrder));
        $responsePdf->assertStatus(200);
        $responsePdf->assertHeader('content-type', 'application/pdf');

        $responsePrint = $this->actingAs($this->user)->get(route('sales.orders.print', $salesOrder));
        $responsePrint->assertStatus(200);
        $responsePrint->assertHeader('content-type', 'application/pdf');
    }

    public function test_invoice_pdf_and_print_renders_with_commercial_invoice_layout(): void
    {
        $invoice = Invoice::create([
            'business_id'     => $this->business->id,
            'customer_id'     => $this->customer->id,
            'invoice_number'  => 'INV-2026-999',
            'invoice_date'    => now()->toDateString(),
            'due_date'        => now()->addDays(14)->toDateString(),
            'status'          => Invoice::STATUS_UNPAID,
            'subtotal'        => 2000000,
            'total_amount'    => 2000000,
            'paid_amount'     => 0,
        ]);

        InvoiceItem::create([
            'invoice_id'     => $invoice->id,
            'product_id'     => $this->product->id,
            'item_name'      => 'Produk Unggulan Resmi',
            'unit_id'        => $this->unit->id,
            'quantity'       => 4,
            'unit_price'     => 500000,
            'subtotal'       => 2000000,
        ]);

        $responsePdf = $this->actingAs($this->user)->get(route('invoices.pdf', $invoice));
        $responsePdf->assertStatus(200);
        $responsePdf->assertHeader('content-type', 'application/pdf');

        $responsePrint = $this->actingAs($this->user)->get(route('invoices.print', $invoice));
        $responsePrint->assertStatus(200);
        $responsePrint->assertHeader('content-type', 'application/pdf');
    }

    public function test_sales_return_pdf_and_print_renders_with_official_return_layout(): void
    {
        $location = Location::create([
            'business_id' => $this->business->id,
            'name'        => 'Gudang Pusat',
            'code'        => 'GD-01',
            'type'        => 'warehouse',
            'is_active'   => true,
        ]);

        $return = SalesReturn::create([
            'business_id'    => $this->business->id,
            'customer_id'    => $this->customer->id,
            'location_id'    => $location->id,
            'return_number'  => 'RET-2026-999',
            'return_date'    => now()->toDateString(),
            'reason'         => 'Kerusakan kemasan',
            'status'         => SalesReturn::STATUS_COMPLETED,
            'total_amount'   => 500000,
            'refund_method'  => SalesReturn::REFUND_CASH,
            'created_by'     => $this->user->id,
        ]);

        SalesReturnItem::create([
            'sales_return_id' => $return->id,
            'product_id'      => $this->product->id,
            'item_name'       => 'Produk Unggulan Resmi',
            'quantity'        => 1,
            'unit_price'      => 500000,
            'subtotal'        => 500000,
        ]);

        $responsePdf = $this->actingAs($this->user)->get(route('sales.returns.pdf', $return));
        $responsePdf->assertStatus(200);
        $responsePdf->assertHeader('content-type', 'application/pdf');

        $responsePrint = $this->actingAs($this->user)->get(route('sales.returns.print', $return));
        $responsePrint->assertStatus(200);
        $responsePrint->assertHeader('content-type', 'application/pdf');
    }

    public function test_pdf_endpoints_strictly_block_cross_tenant_access(): void
    {
        $competingBusiness = Business::create(['name' => 'PT Kompetitor Usaha']);
        $otherUser = User::create([
            'name'               => 'Penyusup Luar',
            'email'              => 'penyusup@kompetitor.test',
            'password'           => bcrypt('secret'),
            'active_business_id' => $competingBusiness->id,
        ]);
        $competingBusiness->users()->attach($otherUser->id, [
            'id'        => (string) Str::uuid(),
            'role'      => 'owner',
            'is_active' => true,
        ]);

        $invoice = Invoice::create([
            'business_id'    => $this->business->id,
            'customer_id'    => $this->customer->id,
            'invoice_number' => 'INV-SECURE-001',
            'invoice_date'   => now()->toDateString(),
            'due_date'       => now()->addDays(7)->toDateString(),
            'status'         => Invoice::STATUS_UNPAID,
            'total_amount'   => 500000,
        ]);

        // Competing tenant must be blocked with 403 Forbidden
        $response = $this->actingAs($otherUser)->get(route('invoices.pdf', $invoice));
        $response->assertStatus(403);
    }

    public function test_sales_order_and_quotation_support_custom_and_default_signatory(): void
    {
        // 1. Sales order with explicit sender_name and recipient_name
        $soCustom = SalesOrder::create([
            'business_id'      => $this->business->id,
            'customer_id'      => $this->customer->id,
            'so_number'        => 'SO-CUSTOM-01',
            'order_date'       => now()->toDateString(),
            'status'           => SalesOrder::STATUS_CONFIRMED,
            'subtotal'         => 500000,
            'total_amount'     => 500000,
            'sender_name'      => 'Ahmad Logistik',
            'recipient_name'   => 'Ibu Siti Purchasing',
            'created_by'       => $this->user->id,
        ]);

        $renderedSoHtml = view('pdf.sales_order_pdf', [
            'salesOrder' => $soCustom,
            'business'   => $this->business,
        ])->render();

        $this->assertStringContainsString('Ahmad Logistik', $renderedSoHtml);
        $this->assertStringContainsString('Ibu Siti Purchasing', $renderedSoHtml);

        // 2. Sales order with null sender_name falls back to creator's name
        $soDefault = SalesOrder::create([
            'business_id'      => $this->business->id,
            'customer_id'      => $this->customer->id,
            'so_number'        => 'SO-DEFAULT-02',
            'order_date'       => now()->toDateString(),
            'status'           => SalesOrder::STATUS_CONFIRMED,
            'subtotal'         => 500000,
            'total_amount'     => 500000,
            'sender_name'      => null,
            'created_by'       => $this->user->id,
        ]);

        $renderedSoDefaultHtml = view('pdf.sales_order_pdf', [
            'salesOrder' => $soDefault,
            'business'   => $this->business,
        ])->render();

        $this->assertStringContainsString($this->user->name, $renderedSoDefaultHtml);

        // 3. Quotation with explicit sender_name and recipient_name
        $quoCustom = Quotation::create([
            'business_id'      => $this->business->id,
            'customer_id'      => $this->customer->id,
            'quotation_number' => 'QUO-CUSTOM-01',
            'date'             => now()->toDateString(),
            'status'           => Quotation::STATUS_DRAFT,
            'subtotal'         => 500000,
            'total_amount'     => 500000,
            'sender_name'      => 'Bambang Sales Manager',
            'recipient_name'   => 'Bapak Hendra Direksi',
            'created_by'       => $this->user->id,
        ]);

        $renderedQuoHtml = view('pdf.quotation_pdf', [
            'quotation' => $quoCustom,
            'business'  => $this->business,
        ])->render();

        $this->assertStringContainsString('Bambang Sales Manager', $renderedQuoHtml);
        $this->assertStringContainsString('Bapak Hendra Direksi', $renderedQuoHtml);

        // 4. Quotation with null sender_name falls back to creator's name
        $quoDefault = Quotation::create([
            'business_id'      => $this->business->id,
            'customer_id'      => $this->customer->id,
            'quotation_number' => 'QUO-DEFAULT-02',
            'date'             => now()->toDateString(),
            'status'           => Quotation::STATUS_DRAFT,
            'subtotal'         => 500000,
            'total_amount'     => 500000,
            'sender_name'      => null,
            'created_by'       => $this->user->id,
        ]);

        $renderedQuoDefaultHtml = view('pdf.quotation_pdf', [
            'quotation' => $quoDefault,
            'business'  => $this->business,
        ])->render();

        $this->assertStringContainsString($this->user->name, $renderedQuoDefaultHtml);
    }

    public function test_quotation_and_invoice_security_idor_protections(): void
    {
        $competingBusiness = Business::create(['name' => 'PT Pesaing Jahat']);
        $foreignCustomer = Customer::create([
            'business_id' => $competingBusiness->id,
            'name'        => 'Pelanggan Rahasia Tenant Lain',
        ]);
        $foreignProduct = Product::create([
            'business_id'    => $competingBusiness->id,
            'output_unit_id' => $this->unit->id,
            'code'           => 'SEC-FOREIGN-01',
            'name'           => 'Produk Rahasia Tenant Lain',
            'selling_price'  => 1000000,
            'is_active'      => true,
        ]);

        // 1. Attempting to create Quotation with foreign customer belonging to another tenant must fail validation
        $response = $this->actingAs($this->user)->post(route('sales.quotations.store'), [
            'customer_id' => $foreignCustomer->id,
            'date'        => now()->toDateString(),
            'items'       => [
                [
                    'product_id'   => $this->product->id,
                    'product_name' => 'Produk Legal',
                    'unit_price'   => 50000,
                    'quantity'     => 1,
                ],
            ],
        ]);
        $response->assertSessionHasErrors(['customer_id']);

        // 2. Attempting to create Quotation with foreign product belonging to another tenant must fail validation
        $responseProduct = $this->actingAs($this->user)->post(route('sales.quotations.store'), [
            'customer_id' => $this->customer->id,
            'date'        => now()->toDateString(),
            'items'       => [
                [
                    'product_id'   => $foreignProduct->id,
                    'product_name' => 'Produk Curian',
                    'unit_price'   => 50000,
                    'quantity'     => 1,
                ],
            ],
        ]);
        $responseProduct->assertSessionHasErrors(['items.0.product_id']);

        // 3. Attempting to access foreign invoice must be blocked with 403
        $foreignInvoice = Invoice::create([
            'business_id'    => $competingBusiness->id,
            'customer_id'    => $foreignCustomer->id,
            'invoice_number' => 'INV-FOREIGN-999',
            'invoice_date'   => now()->toDateString(),
            'due_date'       => now()->addDays(7)->toDateString(),
            'status'         => Invoice::STATUS_UNPAID,
            'total_amount'   => 500000,
        ]);

        $res = $this->actingAs($this->user)->get(route('invoices.show', $foreignInvoice));
        $this->assertTrue(in_array($res->status(), [403, 404], true));
    }
}
