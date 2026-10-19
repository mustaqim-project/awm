<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Commerce\InvoiceNumberGenerator;
use App\Domain\Commerce\InvoiceService;
use App\Exports\InvoiceReportExport;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Unit;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class InvoiceWebController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService = new InvoiceService,
        private readonly InvoiceNumberGenerator $numberGenerator = new InvoiceNumberGenerator
    ) {}

    /**
     * Display a listing of invoices with KPIs.
     */
    public function index(Request $request): View
    {
        $business = Context::requireBusiness();

        $query = Invoice::with(['customer', 'items', 'payments'])
            ->latest('invoice_date');

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%")->orWhere('company_name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $invoices = $query->paginate(15)->withQueryString();

        // Financial KPIs
        $totalInvoiced = (float) Invoice::where('status', '!=', Invoice::STATUS_VOID)->sum('total_amount');
        $totalPaid = (float) Invoice::where('status', '!=', Invoice::STATUS_VOID)->sum('paid_amount');
        $totalReceivables = (float) Invoice::whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_UNPAID, Invoice::STATUS_PARTIALLY_PAID, Invoice::STATUS_OVERDUE])->sum('balance_due');
        $totalGrossProfit = (float) Invoice::where('status', '!=', Invoice::STATUS_VOID)->sum('total_gross_profit');

        return view('app.invoices.index', compact(
            'business',
            'invoices',
            'totalInvoiced',
            'totalPaid',
            'totalReceivables',
            'totalGrossProfit'
        ));
    }

    /**
     * Show form for creating a new invoice (from products, PO, or HPP calculator).
     */
    public function create(Request $request): View
    {
        $business = Context::requireBusiness();

        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->with('outputUnit')->orderBy('name')->get();
        $units = Unit::all();
        $locations = \App\Models\Location::where('is_active', true)->orderBy('name')->get();

        // Available confirmed POs that can be converted
        $availablePurchaseOrders = PurchaseOrder::where('po_type', PurchaseOrder::TYPE_CUSTOMER)
            ->whereIn('status', [PurchaseOrder::STATUS_CONFIRMED, PurchaseOrder::STATUS_PARTIALLY_INVOICED])
            ->with(['customer', 'items.unit'])
            ->latest()
            ->get();

        // Check if pre-filled from PO
        $prefillPoId = $request->get('po_id');
        $selectedPo = $prefillPoId ? PurchaseOrder::with(['customer', 'items.unit'])->find($prefillPoId) : null;

        // Check if pre-filled from Calculator HPP
        $prefillProductId = $request->get('product_id');
        $prefillQty = (float) ($request->get('qty', 1));
        $prefillPrice = (float) ($request->get('price', 0));
        $prefillCost = (float) ($request->get('cost', 0));

        $prefillProduct = $prefillProductId ? Product::find($prefillProductId) : null;

        return view('app.invoices.create', compact(
            'business',
            'customers',
            'products',
            'units',
            'locations',
            'availablePurchaseOrders',
            'selectedPo',
            'prefillProduct',
            'prefillQty',
            'prefillPrice',
            'prefillCost'
        ));
    }

    /**
     * Store a new invoice with line items.
     */
    public function store(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'customer_id' => [
                'required',
                Rule::exists('customers', 'id')->where('business_id', $business->id),
            ],
            'location_id' => [
                'nullable',
                Rule::exists('locations', 'id')->where('business_id', $business->id),
            ],
            'purchase_order_id' => [
                'nullable',
                Rule::exists('purchase_orders', 'id')->where('business_id', $business->id),
            ],
            'invoice_number' => ['nullable', 'string', 'max:100'],
            'sender_name' => ['nullable', 'string', 'max:150'],
            'recipient_name' => ['nullable', 'string', 'max:150'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'in:draft,sent,unpaid'],
            'discount_type' => ['nullable', 'string', 'in:fixed,percentage'],
            'discount_value' => ['nullable', 'numeric', 'gte:0'],
            'tax_percentage' => ['nullable', 'numeric', 'gte:0', 'lte:100'],
            'shipping_cost' => ['nullable', 'numeric', 'gte:0'],
            'payment_terms' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'terms_conditions' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'nullable',
                Rule::exists('products', 'id')->where('business_id', $business->id),
            ],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.sku' => ['nullable', 'string', 'max:100'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_id' => ['required', 'exists:units,id'],
            'items.*.unit_price' => ['required', 'numeric', 'gte:0'],
            'items.*.unit_hpp' => ['nullable', 'numeric', 'gte:0'],
        ]);

        /** @var Customer $customer */
        $customer = Customer::findOrFail($validated['customer_id']);

        $invoice = $this->invoiceService->createFromProducts($business, $customer, $validated['items'], $validated);

        return redirect()->route('invoices.show', $invoice->id)->with('success', "Faktur {$invoice->invoice_number} berhasil diterbitkan.");
    }

    /**
     * Show form for editing an existing invoice (when unpaid and not void).
     */
    public function edit(Invoice $invoice): View|RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($invoice->business_id === $business->id, 403);

        if (in_array($invoice->status, [Invoice::STATUS_PAID, Invoice::STATUS_VOID], true) || (float) $invoice->paid_amount > 0) {
            return redirect()->route('invoices.show', $invoice->id)
                ->with('error', "Faktur [{$invoice->invoice_number}] tidak dapat diedit karena sudah lunas, dibatalkan, atau memiliki pembayaran tercatat.");
        }

        $invoice->load(['customer', 'purchaseOrder', 'items.product', 'items.unit']);

        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->with('outputUnit')->orderBy('name')->get();
        $units = Unit::all();
        $locations = \App\Models\Location::where('is_active', true)->orderBy('name')->get();

        return view('app.invoices.edit', compact(
            'business',
            'invoice',
            'customers',
            'products',
            'units',
            'locations'
        ));
    }

    /**
     * Update an existing invoice with line items.
     */
    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($invoice->business_id === $business->id, 403);

        if (in_array($invoice->status, [Invoice::STATUS_PAID, Invoice::STATUS_VOID], true) || (float) $invoice->paid_amount > 0) {
            return redirect()->route('invoices.show', $invoice->id)
                ->with('error', 'Faktur yang sudah lunas atau memiliki pembayaran tercatat tidak dapat disunting.');
        }

        $validated = $request->validate([
            'customer_id' => [
                'required',
                Rule::exists('customers', 'id')->where('business_id', $business->id),
            ],
            'location_id' => [
                'nullable',
                Rule::exists('locations', 'id')->where('business_id', $business->id),
            ],
            'sender_name' => ['nullable', 'string', 'max:150'],
            'recipient_name' => ['nullable', 'string', 'max:150'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'discount_type' => ['nullable', 'string', 'in:fixed,percentage'],
            'discount_value' => ['nullable', 'numeric', 'gte:0'],
            'tax_percentage' => ['nullable', 'numeric', 'gte:0', 'lte:100'],
            'shipping_cost' => ['nullable', 'numeric', 'gte:0'],
            'payment_terms' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'terms_conditions' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'nullable',
                Rule::exists('products', 'id')->where('business_id', $business->id),
            ],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.sku' => ['nullable', 'string', 'max:100'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_id' => ['required', 'exists:units,id'],
            'items.*.unit_price' => ['required', 'numeric', 'gte:0'],
            'items.*.unit_hpp' => ['nullable', 'numeric', 'gte:0'],
        ]);

        /** @var Customer $customer */
        $customer = Customer::findOrFail($validated['customer_id']);

        $this->invoiceService->updateDraft($invoice, $customer, $validated['items'], $validated);

        return redirect()->route('invoices.show', $invoice->id)->with('success', "Draf Faktur {$invoice->invoice_number} berhasil diperbarui.");
    }

    /**
     * Display detailed invoice view.
     */
    public function show(Invoice $invoice): View
    {
        $business = Context::requireBusiness();
        abort_unless($invoice->business_id === $business->id, 403);

        $invoice->load(['customer', 'purchaseOrder', 'items.product', 'items.unit', 'payments.creator']);

        return view('app.invoices.show', compact('business', 'invoice'));
    }

    /**
     * Record payment receipt for an invoice.
     */
    public function recordPayment(Request $request, Invoice $invoice): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($invoice->business_id === $business->id, 403);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'in:bank_transfer,cash,qris,credit_card,cheque'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $this->invoiceService->recordPayment(
            $invoice,
            (float) $validated['amount'],
            $validated['payment_method'],
            [
                'payment_date' => $validated['payment_date'],
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]
        );

        return back()->with('success', "Pembayaran sebesar Rp " . number_format((float) $validated['amount'], 0, ',', '.') . " berhasil dicatat.");
    }

    /**
     * Stream or print professional commercial tax invoice PDF layout (A4).
     */
    public function print(Invoice $invoice): \Illuminate\Http\Response
    {
        return $this->pdf($invoice);
    }

    /**
     * Download or stream formal business correspondence PDF for Invoice (A4).
     */
    public function pdf(Invoice $invoice): \Illuminate\Http\Response
    {
        $business = Context::requireBusiness();
        abort_unless($invoice->business_id === $business->id, 403);

        $invoice->load(['customer', 'purchaseOrder', 'items.product', 'items.unit', 'payments.creator', 'salesOrder']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice_pdf', [
            'business' => $business,
            'invoice' => $invoice,
        ])->setPaper('a4', 'portrait');

        $filename = 'Faktur-' . \Illuminate\Support\Str::slug($invoice->invoice_number) . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Export all invoices to Excel XLSX format (or CSV fallback).
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $business = Context::requireBusiness();

        $invoices = Invoice::with(['customer', 'items', 'payments', 'purchaseOrder'])
            ->latest('invoice_date')
            ->get();

        if ($request->query('format') !== 'csv') {
            $exporter = new InvoiceReportExport();
            return $exporter->download($business, $invoices);
        }

        $filename = 'Laporan_Faktur_Penjualan_' . \Illuminate\Support\Str::slug($business->name) . '_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($invoices, $business): void {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel compatibility

            fputcsv($file, ['LAPORAN LENGKAP FAKTUR PENJUALAN & PENAGIHAN']);
            fputcsv($file, ['Nama Bisnis', $business->name]);
            fputcsv($file, ['Mata Uang', $business->currency_code . ' (' . $business->currency_symbol . ')']);
            fputcsv($file, ['Waktu Export', date('d F Y H:i:s')]);
            fputcsv($file, []);

            fputcsv($file, [
                'No',
                'Nomor Faktur',
                'Nama Pengirim',
                'Nama Penerima',
                'Tanggal Faktur',
                'Jatuh Tempo',
                'Nama Pelanggan',
                'Perusahaan',
                'No. PO Referensi',
                'Status',
                'Subtotal',
                'Diskon',
                'PPN / Pajak',
                'Ongkos Kirim',
                'Total Tagihan',
                'Sudah Dibayar',
                'Sisa Saldo (Piutang)',
                'Snapshot Total HPP',
                'Laba Kotor Riil',
                'Termin Bayar',
            ]);

            $no = 1;
            foreach ($invoices as $inv) {
                fputcsv($file, [
                    $no++,
                    $inv->invoice_number,
                    $inv->sender_name ?: $business->name,
                    $inv->recipient_name ?: ($inv->customer?->name ?? '-'),
                    $inv->invoice_date?->format('Y-m-d'),
                    $inv->due_date?->format('Y-m-d'),
                    $inv->customer?->name ?? '-',
                    $inv->customer?->company_name ?? '-',
                    $inv->purchaseOrder?->po_number ?? '-',
                    strtoupper($inv->status),
                    round((float) $inv->subtotal),
                    round((float) $inv->discount_amount),
                    round((float) $inv->tax_amount),
                    round((float) $inv->shipping_cost),
                    round((float) $inv->total_amount),
                    round((float) $inv->paid_amount),
                    round((float) $inv->balance_due),
                    round((float) $inv->total_hpp_cost),
                    round((float) $inv->total_gross_profit),
                    $inv->payment_terms ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Delete an invoice (Only allowed for draft invoices; issued/active invoices must be voided/cancelled).
     */
    public function destroy(Invoice $invoice): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($invoice->business_id === $business->id, 403);

        if ($invoice->status !== Invoice::STATUS_DRAFT) {
            return back()->with('error', "Faktur dengan status [{$invoice->status}] tidak dapat dihapus untuk menjaga keabsahan pembukuan bisnis. Gunakan opsi Pembatalan / Void.");
        }

        if ($invoice->payments()->exists()) {
            return back()->with('error', 'Faktur yang telah memiliki riwayat pembayaran tidak dapat dihapus.');
        }

        $invNumber = $invoice->invoice_number;
        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', "Draf Faktur {$invNumber} berhasil dihapus.");
    }

    /**
     * Confirm and release an invoice: Deduct inventory stock and generate auto-journal.
     */
    public function confirm(Request $request, Invoice $invoice): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($invoice->business_id === $business->id, 403);

        $locationId = $request->get('location_id');

        try {
            $this->invoiceService->confirmAndRelease($invoice, $locationId);

            return back()->with('success', "Faktur {$invoice->invoice_number} berhasil dikonfirmasi & dirilis. Stok barang otomatis terpotong dan jurnal piutang telah tercatat.");
        } catch (\App\Domain\Inventory\Exceptions\InsufficientStockException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', "Gagal merilis faktur: {$e->getMessage()}");
        }
    }

    /**
     * Void an invoice: Restores deducted stock and marks invoice as void.
     */
    public function void(Request $request, Invoice $invoice): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($invoice->business_id === $business->id, 403);

        $reason = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ])['reason'] ?? null;

        try {
            $this->invoiceService->voidInvoice($invoice, $reason);

            return back()->with('success', "Faktur {$invoice->invoice_number} telah dibatalkan (VOID) dan stok barang telah dikembalikan.");
        } catch (\Throwable $e) {
            return back()->with('error', "Gagal membatalkan faktur: {$e->getMessage()}");
        }
    }

    /**
     * Send payment term reminder to customer via WhatsApp and/or Email.
     */
    public function sendTermReminder(
        Request $request,
        Invoice $invoice,
        \App\Domain\Crm\CustomerPaymentTermReminderService $reminderService
    ): RedirectResponse {
        $business = Context::requireBusiness();
        abort_unless($invoice->business_id === $business->id, 403);

        if ($invoice->balance_due <= 0 || $invoice->status === Invoice::STATUS_VOID || $invoice->status === Invoice::STATUS_DRAFT) {
            return back()->with('error', 'Pengingat hanya dapat dikirim untuk faktur aktif yang masih memiliki sisa tagihan.');
        }

        $validated = $request->validate([
            'channel' => ['nullable', 'string', 'in:whatsapp,email,both'],
            'custom_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $channel = $validated['channel'] ?? 'both';
        $customNotes = $validated['custom_notes'] ?? null;

        $result = $reminderService->dispatchInvoiceReminder(
            $invoice,
            \App\Models\CustomerTermReminder::TYPE_MANUAL,
            $channel,
            $customNotes
        );

        if ($result['success']) {
            $msg = "Pengingat termin faktur {$invoice->invoice_number} berhasil dikirim ke pelanggan.";
            return back()->with('success', $msg)->with('wa_url', $result['wa_url'] ?? null);
        }

        $errorMsg = $result['error'] ?: 'Gagal mengirim pengingat ke saluran terpilih.';
        return back()->with('error', $errorMsg)->with('wa_url', $result['wa_url'] ?? null);
    }
}


