<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Location;
use App\Models\Product;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class SalesReturnPrintPdfTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private Customer $customer;
    private SalesReturn $return;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create([
            'name'     => 'Store Owner',
            'email'    => 'owner@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->business = Business::create([
            'name'            => 'PT Kopi Enak Nusantara',
            'address'         => 'Jl. Sudirman No. 123, Jakarta',
            'phone'           => '08123456789',
            'email'           => 'halo@kopienak.com',
            'currency'        => 'IDR',
        ]);

        $this->business->users()->attach($this->user->id, [
            'id'        => (string) Str::uuid(),
            'role'      => 'owner',
            'is_active' => true,
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $unit = Unit::create([
            'business_id' => $this->business->id,
            'code'        => 'PCS',
            'name'        => 'Pieces',
            'category'    => Unit::CATEGORY_QUANTITY,
            'is_base'     => true,
        ]);

        $location = Location::create([
            'business_id' => $this->business->id,
            'name'        => 'Gudang Pusat',
            'code'        => 'GD-01',
            'type'        => 'warehouse',
            'is_active'   => true,
        ]);

        $this->customer = Customer::create([
            'business_id'     => $this->business->id,
            'name'            => 'Budi Santoso',
            'company_name'    => 'CV Maju Jaya',
            'billing_address' => 'Jl. Thamrin No. 45',
            'phone'           => '081999888777',
        ]);

        $product = Product::create([
            'business_id'    => $this->business->id,
            'output_unit_id' => $unit->id,
            'code'           => 'KOP-001',
            'name'           => 'Kopi Arabika Espresso',
            'selling_price'  => 50000,
            'is_active'      => true,
        ]);

        $invoice = Invoice::create([
            'business_id'     => $this->business->id,
            'customer_id'     => $this->customer->id,
            'invoice_number'  => 'INV-2026-0099',
            'invoice_date'    => now()->toDateString(),
            'due_date'        => now()->addDays(30)->toDateString(),
            'status'          => Invoice::STATUS_UNPAID,
            'total_amount'    => 100000,
        ]);

        $invoiceItem = InvoiceItem::create([
            'invoice_id'     => $invoice->id,
            'product_id'     => $product->id,
            'item_name'      => 'Kopi Arabika Espresso',
            'unit_id'        => $unit->id,
            'quantity'       => 2,
            'unit_price'     => 50000,
            'subtotal'       => 100000,
        ]);

        $this->return = SalesReturn::create([
            'business_id'    => $this->business->id,
            'invoice_id'     => $invoice->id,
            'customer_id'    => $this->customer->id,
            'location_id'    => $location->id,
            'return_number'  => 'RET-2026-0042',
            'return_date'    => now()->toDateString(),
            'reason'         => 'Kemasan robek saat pengiriman',
            'status'         => SalesReturn::STATUS_COMPLETED,
            'total_amount'   => 50000,
            'refund_method'  => SalesReturn::REFUND_CASH,
            'created_by'     => $this->user->id,
        ]);

        SalesReturnItem::create([
            'sales_return_id' => $this->return->id,
            'invoice_item_id' => $invoiceItem->id,
            'product_id'      => $product->id,
            'item_name'       => 'Kopi Arabika Espresso',
            'quantity'        => 1,
            'unit_price'      => 50000,
            'subtotal'        => 50000,
        ]);
    }

    public function test_sales_return_show_renders_print_button_and_print_header(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('sales.returns.show', $this->return));

        $response->assertStatus(200);
        $response->assertSee(route('sales.returns.pdf', $this->return));
        $response->assertSee('RET-2026-0042');
    }

    public function test_sales_return_print_page_renders_clean_a4_pdf_layout(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('sales.returns.print', $this->return));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_sales_return_print_enforces_tenant_security(): void
    {
        $otherBusiness = Business::create(['name' => 'Competitor Corp']);
        $otherUser = User::create([
            'name'     => 'Other User',
            'email'    => 'other@example.com',
            'password' => bcrypt('password'),
            'active_business_id' => $otherBusiness->id,
        ]);
        $otherBusiness->users()->attach($otherUser->id, [
            'id'        => (string) Str::uuid(),
            'role'      => 'owner',
            'is_active' => true,
        ]);

        $response = $this->actingAs($otherUser)
            ->get(route('sales.returns.print', $this->return));

        $response->assertStatus(403);
    }
}
