<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SalesOrderAndQuotationEditTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;
    private Customer $customer;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->business = Business::create([
            'name' => 'PT Cooca Retail',
            'slug' => 'pt-cooca-retail',
            'email' => 'retail@cooca.test',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
        ]);

        $this->owner = User::create([
            'name' => 'Owner Cooca',
            'email' => 'owner@cooca.test',
            'phone' => '081234567890',
            'password' => 'password',
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'role' => 'owner',
        ]);
        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $this->customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Pelanggan Setia',
            'email' => 'pelanggan@test.id',
            'phone' => '08991234567',
            'is_active' => true,
        ]);

        $unit = \App\Models\Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Pcs',
            'code' => 'pcs',
            'symbol' => 'pcs',
            'category' => 'quantity',
        ]);

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Kopi Arabika Premium',
            'sku' => 'KOP-001',
            'price' => 50000,
            'type' => 'goods',
            'output_unit_id' => $unit->id,
            'is_active' => true,
        ]);
    }

    public function test_can_render_quotation_edit_page(): void
    {
        Context::setBusiness($this->business);

        $quotation = Quotation::create([
            'business_id' => $this->business->id,
            'customer_id' => $this->customer->id,
            'quotation_number' => 'QUO-202610-0001',
            'date' => now()->toDateString(),
            'status' => 'draft',
            'subtotal' => 100000,
            'total_amount' => 100000,
            'subtotal' => 100000,
            'sender_name' => 'Direktur Utama',
            'recipient_name' => 'Bapak Pembeli',
        ]);

        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'unit_price' => 50000,
            'quantity' => 2,
            'total_amount' => 100000,
            'subtotal' => 100000,
        ]);

        $response = $this->actingAs($this->owner)->get(route('sales.quotations.edit', $quotation->id));

        $response->assertStatus(200);
        $response->assertSee('QUO-202610-0001');
        $response->assertSee('Kopi Arabika Premium');
        $response->assertSee('Direktur Utama');
        $response->assertSee('Bapak Pembeli');
    }

    public function test_can_render_sales_order_edit_page_with_and_without_quotation(): void
    {
        Context::setBusiness($this->business);

        $order = SalesOrder::create([
            'business_id' => $this->business->id,
            'customer_id' => $this->customer->id,
            'so_number' => 'SO-202610-0001',
            'order_date' => now()->toDateString(),
            'status' => 'draft',
            'subtotal' => 150000,
            'total_amount' => 150000,
            'sender_name' => 'Manager Logistik',
            'recipient_name' => 'PIC Gudang Penerima',
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'unit_price' => 50000,
            'quantity' => 3,
            'subtotal' => 150000,
        ]);

        $response = $this->actingAs($this->owner)->get(route('sales.orders.edit', $order->id));

        $response->assertStatus(200);
        $response->assertSee('SO-202610-0001');
        $response->assertSee('Kopi Arabika Premium');
        $response->assertSee('Manager Logistik');
        $response->assertSee('PIC Gudang Penerima');
    }
}
