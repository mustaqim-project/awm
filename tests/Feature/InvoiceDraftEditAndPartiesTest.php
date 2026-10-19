<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Commerce\InvoiceService;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class InvoiceDraftEditAndPartiesTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;
    private Customer $customer;
    private Unit $unit;
    private InvoiceService $invoiceService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->invoiceService = new InvoiceService();

        $this->business = Business::create([
            'name' => 'PT Mitra Sejahtera',
            'slug' => 'pt-mitra-sejahtera',
            'email' => 'admin@mitra.test',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
        ]);

        $this->owner = User::create([
            'name' => 'Owner Mitra',
            'email' => 'owner@mitra.test',
            'phone' => '081234567899',
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
            'name' => 'Budi Santoso',
            'company_name' => 'CV Makmur Bersama',
            'email' => 'budi@makmur.test',
            'payment_terms_days' => 30,
            'is_active' => true,
        ]);

        $this->unit = Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Pieces',
            'code' => 'pcs',
            'symbol' => 'pcs',
            'category' => 'quantity',
        ]);
    }

    public function test_can_create_invoice_with_sender_recipient_and_multiline_notes(): void
    {
        Context::setBusiness($this->business);

        $notes = "Catatan baris 1\nCatatan baris 2\nCatatan baris 3";

        $response = $this->actingAs($this->owner)->post(route('invoices.store'), [
            'customer_id' => $this->customer->id,
            'sender_name' => 'Bambang Bagian Penjualan',
            'recipient_name' => 'Ibu Ratna Purchasing',
            'invoice_date' => '2026-10-12',
            'due_date' => '2026-11-12',
            'status' => 'draft',
            'notes' => $notes,
            'items' => [
                [
                    'item_name' => 'Kopi Robusta 1kg',
                    'quantity' => 5,
                    'unit_id' => $this->unit->id,
                    'unit_price' => 100000,
                    'unit_hpp' => 70000,
                ],
            ],
        ]);

        $response->assertRedirect();
        $invoice = Invoice::first();

        $this->assertNotNull($invoice);
        $this->assertSame('draft', $invoice->status);
        $this->assertSame('Bambang Bagian Penjualan', $invoice->sender_name);
        $this->assertSame('Ibu Ratna Purchasing', $invoice->recipient_name);
        $this->assertSame($notes, $invoice->notes);
        $this->assertEquals(500000, $invoice->total_amount);
    }

    public function test_can_view_edit_page_for_draft_invoice(): void
    {
        Context::setBusiness($this->business);

        $invoice = $this->invoiceService->createFromProducts(
            $this->business,
            $this->customer,
            [
                [
                    'item_name' => 'Item Draf',
                    'quantity' => 2,
                    'unit_id' => $this->unit->id,
                    'unit_price' => 50000,
                ],
            ],
            [
                'sender_name' => 'Pengirim Lama',
                'recipient_name' => 'Penerima Lama',
                'notes' => "Catatan 1\nCatatan 2",
            ]
        );

        $response = $this->actingAs($this->owner)->get(route('invoices.edit', $invoice->id));

        $response->assertOk();
        $response->assertSee('Edit Draf Faktur');
        $response->assertSee('Pengirim Lama');
        $response->assertSee('Penerima Lama');
    }

    public function test_can_update_draft_invoice_with_new_parties_and_multiline_notes(): void
    {
        Context::setBusiness($this->business);

        $invoice = $this->invoiceService->createFromProducts(
            $this->business,
            $this->customer,
            [
                [
                    'item_name' => 'Item Awal',
                    'quantity' => 1,
                    'unit_id' => $this->unit->id,
                    'unit_price' => 100000,
                ],
            ],
            [
                'sender_name' => 'Pengirim Lama',
                'recipient_name' => 'Penerima Lama',
            ]
        );

        $newNotes = "Catatan revisi baris 1\nCatatan revisi baris 2\nCatatan revisi baris 3";

        $response = $this->actingAs($this->owner)->put(route('invoices.update', $invoice->id), [
            'customer_id' => $this->customer->id,
            'sender_name' => 'Pengirim Baru Super',
            'recipient_name' => 'Penerima Baru Utama',
            'invoice_date' => '2026-10-15',
            'due_date' => '2026-11-15',
            'notes' => $newNotes,
            'items' => [
                [
                    'item_name' => 'Item Revisi Baru',
                    'quantity' => 3,
                    'unit_id' => $this->unit->id,
                    'unit_price' => 120000,
                    'unit_hpp' => 80000,
                ],
            ],
        ]);

        $response->assertRedirect(route('invoices.show', $invoice->id));

        $invoice->refresh();
        $this->assertSame('Pengirim Baru Super', $invoice->sender_name);
        $this->assertSame('Penerima Baru Utama', $invoice->recipient_name);
        $this->assertSame($newNotes, $invoice->notes);
        $this->assertEquals(360000, $invoice->total_amount);
        $this->assertSame('Item Revisi Baru', $invoice->items->first()->item_name);
    }

    public function test_cannot_edit_non_draft_invoice(): void
    {
        Context::setBusiness($this->business);

        $invoice = $this->invoiceService->createFromProducts(
            $this->business,
            $this->customer,
            [
                [
                    'item_name' => 'Item Dirilis',
                    'quantity' => 1,
                    'unit_id' => $this->unit->id,
                    'unit_price' => 100000,
                ],
            ]
        );

        // Mark as paid or void (non-editable)
        $invoice->update(['status' => Invoice::STATUS_PAID, 'paid_amount' => 100000]);

        // Attempt to GET edit page
        $editResponse = $this->actingAs($this->owner)->get(route('invoices.edit', $invoice->id));
        $editResponse->assertRedirect(route('invoices.show', $invoice->id));
        $editResponse->assertSessionHas('error');

        // Attempt to PUT update
        $updateResponse = $this->actingAs($this->owner)->put(route('invoices.update', $invoice->id), [
            'customer_id' => $this->customer->id,
            'invoice_date' => '2026-10-15',
            'items' => [
                [
                    'item_name' => 'Hacked Item',
                    'quantity' => 1,
                    'unit_id' => $this->unit->id,
                    'unit_price' => 1000,
                ],
            ],
        ]);
        $updateResponse->assertRedirect(route('invoices.show', $invoice->id));
        $updateResponse->assertSessionHas('error');
    }
}
