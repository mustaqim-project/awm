<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Commerce\InvoiceService;
use App\Domain\Commerce\SalesReturnService;
use App\Domain\Sales\SalesPipelineService;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Location;
use App\Models\PaymentAccount;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\Unit;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class AdminB2bController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly SalesPipelineService $pipelineService = new SalesPipelineService(),
        private readonly InvoiceService $invoiceService = new InvoiceService(),
        private readonly SalesReturnService $returnService = new SalesReturnService(),
    ) {}

    /**
     * Map controller actions to granular platform admin permissions.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('require.admin.permission:admin.b2b.view', only: [
                'index', 'pdfQuotation', 'pdfSalesOrder', 'pdfInvoice', 'pdfSalesReturn', 'businessResources',
            ]),
            new Middleware('require.admin.permission:admin.b2b.create', only: [
                'storeQuotation', 'storeSalesOrder', 'storeInvoice', 'storeSalesReturn',
            ]),
            new Middleware('require.admin.permission:admin.b2b.edit', only: [
                'updateQuotation', 'updateSalesOrder', 'updateInvoice', 'voidInvoice',
                'updateSalesReturn', 'rejectSalesReturn',
            ]),
            new Middleware('require.admin.permission:admin.b2b.delete', only: [
                'destroyQuotation', 'destroySalesOrder', 'destroyInvoice', 'destroySalesReturn',
            ]),
            new Middleware('require.admin.permission:admin.b2b.manage', only: [
                'convertQuotation', 'generateInvoiceFromOrder', 'recordInvoicePayment',
                'approveSalesReturn', 'completeSalesReturn',
            ]),
            new Middleware('require.admin.permission:admin.b2b.export', only: ['export']),
        ];
    }

    /**
     * Display the B2B Sales & Invoicing Command Center.
     */
    public function index(Request $request): View
    {
        $activeTab  = $request->input('tab', 'quotations');
        $businessId = $request->input('business_id');
        $status     = $request->input('status');
        $startDate  = $request->input('start_date');
        $endDate    = $request->input('end_date');
        $search     = $request->input('search');

        // 1. Global KPI Metrics (filtered by business if chosen)
        $kpiBaseQuotation  = Quotation::query();
        $kpiBaseOrder      = SalesOrder::query();
        $kpiBaseInvoice    = Invoice::query();
        $kpiBaseReturn     = SalesReturn::query();

        if ($businessId) {
            $kpiBaseQuotation->where('business_id', $businessId);
            $kpiBaseOrder->where('business_id', $businessId);
            $kpiBaseInvoice->where('business_id', $businessId);
            $kpiBaseReturn->where('business_id', $businessId);
        }

        $kpi = [
            'totalQuotationAmount' => (float) (clone $kpiBaseQuotation)->sum('total_amount'),
            'totalQuotationCount'  => (clone $kpiBaseQuotation)->count(),
            'acceptedQuotationCount' => (clone $kpiBaseQuotation)->where('status', Quotation::STATUS_ACCEPTED)->count(),

            'totalOrderAmount'     => (float) (clone $kpiBaseOrder)->sum('total_amount'),
            'totalOrderCount'      => (clone $kpiBaseOrder)->count(),
            'fulfilledOrderCount'  => (clone $kpiBaseOrder)->where('status', SalesOrder::STATUS_FULFILLED)->count(),

            'totalInvoiceAmount'   => (float) (clone $kpiBaseInvoice)->sum('total_amount'),
            'totalInvoicePaid'     => (float) (clone $kpiBaseInvoice)->sum('paid_amount'),
            'totalInvoiceBalance'  => (float) (clone $kpiBaseInvoice)->sum('balance_due'),
            'overdueInvoiceCount'  => (clone $kpiBaseInvoice)->where('status', Invoice::STATUS_OVERDUE)->count(),

            'totalReturnAmount'    => (float) (clone $kpiBaseReturn)->sum('total_amount'),
            'totalReturnCount'     => (clone $kpiBaseReturn)->count(),
            'completedReturnCount' => (clone $kpiBaseReturn)->where('status', SalesReturn::STATUS_COMPLETED)->count(),
        ];

        // 2. Data for active tab
        $quotations = null;
        $orders     = null;
        $invoices   = null;
        $returns    = null;

        if ($activeTab === 'quotations') {
            $query = Quotation::query()
                ->with(['business', 'customer', 'creator', 'salesOrder']);

            if ($businessId) $query->where('business_id', $businessId);
            if ($status) $query->where('status', $status);
            if ($startDate && $endDate) $query->whereBetween('date', [$startDate, $endDate]);
            if ($search) {
                $query->where(function ($q) use ($search): void {
                    $q->where('quotation_number', 'like', "%{$search}%")
                        ->orWhere('recipient_name', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%")->orWhere('company_name', 'like', "%{$search}%"));
                });
            }

            $quotations = $query->orderBy('date', 'desc')->paginate(20, ['*'], 'q_page')->withQueryString();
        } elseif ($activeTab === 'orders') {
            $query = SalesOrder::query()
                ->with(['business', 'customer', 'creator', 'quotation', 'invoices']);

            if ($businessId) $query->where('business_id', $businessId);
            if ($status) $query->where('status', $status);
            if ($startDate && $endDate) $query->whereBetween('order_date', [$startDate, $endDate]);
            if ($search) {
                $query->where(function ($q) use ($search): void {
                    $q->where('so_number', 'like', "%{$search}%")
                        ->orWhere('recipient_name', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%")->orWhere('company_name', 'like', "%{$search}%"));
                });
            }

            $orders = $query->orderBy('order_date', 'desc')->paginate(20, ['*'], 'so_page')->withQueryString();
        } elseif ($activeTab === 'invoices') {
            $query = Invoice::query()
                ->with(['business', 'customer', 'creator', 'salesOrder', 'payments']);

            if ($businessId) $query->where('business_id', $businessId);
            if ($status) $query->where('status', $status);
            if ($startDate && $endDate) $query->whereBetween('invoice_date', [$startDate, $endDate]);
            if ($search) {
                $query->where(function ($q) use ($search): void {
                    $q->where('invoice_number', 'like', "%{$search}%")
                        ->orWhere('recipient_name', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%")->orWhere('company_name', 'like', "%{$search}%"));
                });
            }

            $invoices = $query->orderBy('invoice_date', 'desc')->paginate(20, ['*'], 'inv_page')->withQueryString();
        } elseif ($activeTab === 'returns') {
            $query = SalesReturn::query()
                ->with(['business', 'customer', 'creator', 'approver', 'completer', 'invoice', 'location']);

            if ($businessId) $query->where('business_id', $businessId);
            if ($status) $query->where('status', $status);
            if ($startDate && $endDate) $query->whereBetween('return_date', [$startDate, $endDate]);
            if ($search) {
                $query->where(function ($q) use ($search): void {
                    $q->where('return_number', 'like', "%{$search}%")
                        ->orWhere('reason', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"));
                });
            }

            $returns = $query->orderBy('return_date', 'desc')->paginate(20, ['*'], 'ret_page')->withQueryString();
        }

        // Master lists for filters and modals
        $businesses = Business::select('id', 'name', 'email', 'phone')->orderBy('name')->get();
        $paymentAccounts = PaymentAccount::where('is_active', true)->get();

        $customers = $businessId
            ? Customer::where('business_id', $businessId)->select('id', 'business_id', 'name', 'company_name', 'phone')->orderBy('name')->get()
            : Customer::select('id', 'business_id', 'name', 'company_name', 'phone')->orderBy('name')->limit(50)->get();
        $products = $businessId
            ? Product::where('business_id', $businessId)->where('is_active', true)->select('id', 'business_id', 'name', 'code', 'selling_price', 'output_unit_id')->orderBy('name')->get()
            : Product::where('is_active', true)->select('id', 'business_id', 'name', 'code', 'selling_price', 'output_unit_id')->orderBy('name')->limit(50)->get();
        $locations = $businessId
            ? Location::where('business_id', $businessId)->where('is_active', true)->select('id', 'business_id', 'name')->orderBy('name')->get()
            : Location::where('is_active', true)->select('id', 'business_id', 'name')->orderBy('name')->limit(50)->get();

        return view('admin.b2b.index', compact(
            'activeTab',
            'businessId',
            'status',
            'startDate',
            'endDate',
            'search',
            'kpi',
            'quotations',
            'orders',
            'invoices',
            'returns',
            'businesses',
            'paymentAccounts',
            'customers',
            'products',
            'locations'
        ));
    }

    /**
     * Download or stream formal Quotation PDF (A4).
     */
    public function pdfQuotation(Quotation $quotation): Response
    {
        $quotation->load(['customer', 'items.product', 'salesOrder', 'business']);

        $pdf = Pdf::loadView('pdf.quotation_pdf', [
            'business'  => $quotation->business,
            'quotation' => $quotation,
        ])->setPaper('a4', 'portrait');

        $filename = 'Penawaran-' . Str::slug($quotation->quotation_number) . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Download or stream formal Sales Order Confirmation PDF (A4).
     */
    public function pdfSalesOrder(SalesOrder $salesOrder): Response
    {
        $salesOrder->load(['customer', 'items.product', 'quotation', 'business']);

        $pdf = Pdf::loadView('pdf.sales_order_pdf', [
            'business'   => $salesOrder->business,
            'salesOrder' => $salesOrder,
        ])->setPaper('a4', 'portrait');

        $filename = 'Pesanan-' . Str::slug($salesOrder->so_number) . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Download or stream formal Commercial Tax Invoice PDF (A4).
     */
    public function pdfInvoice(Invoice $invoice): Response
    {
        $invoice->load(['customer', 'purchaseOrder', 'items.product', 'items.unit', 'payments.creator', 'salesOrder', 'business']);

        $pdf = Pdf::loadView('pdf.invoice_pdf', [
            'business' => $invoice->business,
            'invoice'  => $invoice,
        ])->setPaper('a4', 'portrait');

        $filename = 'Faktur-' . Str::slug($invoice->invoice_number) . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Download or stream formal Sales Return / Credit Note PDF (A4).
     */
    public function pdfSalesReturn(SalesReturn $salesReturn): Response
    {
        $salesReturn->load(['invoice.customer', 'posOrder.customer', 'customer', 'items.product', 'location', 'business', 'approver', 'completer']);

        $pdf = Pdf::loadView('pdf.sales_return_pdf', [
            'business' => $salesReturn->business,
            'return'   => $salesReturn,
        ])->setPaper('a4', 'portrait');

        $filename = 'Nota-Retur-' . Str::slug($salesReturn->return_number) . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Convert an accepted Quotation to a Sales Order.
     */
    public function convertQuotation(Quotation $quotation): RedirectResponse
    {
        if ($quotation->salesOrder()->exists()) {
            return back()->with('error', "Penawaran {$quotation->quotation_number} telah memiliki Pesanan Penjualan (SO).");
        }

        $salesOrder = $this->pipelineService->convertQuotationToSalesOrder($quotation);

        return redirect()->route('admin.b2b.index', ['tab' => 'orders', 'business_id' => $quotation->business_id])
            ->with('status', "Surat Penawaran {$quotation->quotation_number} berhasil dikonversi menjadi Pesanan Penjualan {$salesOrder->so_number}!");
    }

    /**
     * Generate an Invoice from a confirmed Sales Order.
     */
    public function generateInvoiceFromOrder(SalesOrder $salesOrder): RedirectResponse
    {
        $invoice = $this->pipelineService->generateInvoiceFromSalesOrder($salesOrder);

        return redirect()->route('admin.b2b.index', ['tab' => 'invoices', 'business_id' => $salesOrder->business_id])
            ->with('status', "Faktur Penjualan {$invoice->invoice_number} berhasil diterbitkan dari Pesanan {$salesOrder->so_number}!");
    }

    /**
     * Record payment receipt against an invoice.
     */
    public function recordInvoicePayment(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'amount'           => ['required', 'numeric', 'min:1', 'max:' . $invoice->balance_due],
            'payment_date'     => ['required', 'date'],
            'payment_method'   => ['required', 'string', 'in:bank_transfer,cash,qris,credit_card,cheque'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ]);

        $adminName = Auth::guard('admin')->user()?->name ?? 'Admin';
        $adminNotes = trim(($validated['notes'] ?? '') . " [Dicatat via Admin Console: {$adminName}]");

        $this->invoiceService->recordPayment(
            $invoice,
            (float) $validated['amount'],
            $validated['payment_method'],
            [
                'payment_date'     => $validated['payment_date'],
                'reference_number' => $validated['reference_number'] ?? null,
                'notes'            => $adminNotes,
                'created_by'       => $invoice->created_by,
            ]
        );

        return redirect()->route('admin.b2b.index', ['tab' => 'invoices', 'business_id' => $invoice->business_id])
            ->with('status', "Pembayaran faktur {$invoice->invoice_number} sebesar Rp " . number_format((float) $validated['amount'], 0, ',', '.') . " berhasil dicatat.");
    }

    /**
     * Approve a pending sales return.
     */
    public function approveSalesReturn(SalesReturn $salesReturn): RedirectResponse
    {
        $this->returnService->approve($salesReturn, (string) Auth::guard('admin')->id());

        return redirect()->route('admin.b2b.index', ['tab' => 'returns', 'business_id' => $salesReturn->business_id])
            ->with('status', "Retur Penjualan {$salesReturn->return_number} berhasil disetujui (Approved)!");
    }

    /**
     * Complete a sales return and process inventory/credit restock.
     */
    public function completeSalesReturn(SalesReturn $salesReturn): RedirectResponse
    {
        $this->returnService->complete($salesReturn, (string) Auth::guard('admin')->id());

        return redirect()->route('admin.b2b.index', ['tab' => 'returns', 'business_id' => $salesReturn->business_id])
            ->with('status', "Retur Penjualan {$salesReturn->return_number} berhasil diselesaikan (Completed) dan stok dipulihkan.");
    }

    /**
     * Export B2B dataset to CSV format.
     */
    public function export(Request $request): StreamedResponse
    {
        $tab = $request->input('tab', 'quotations');
        $filename = 'b2b_' . $tab . '_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($tab): void {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

            if ($tab === 'quotations') {
                fputcsv($handle, ['No. Penawaran', 'Bisnis / Tenant', 'Klien', 'Tgl Terbit', 'Masa Berlaku', 'Nilai Total (IDR)', 'Status']);
                Quotation::with(['business', 'customer'])->orderBy('date', 'desc')->chunk(200, function ($rows) use ($handle): void {
                    foreach ($rows as $q) {
                        fputcsv($handle, [
                            $q->quotation_number,
                            $q->business?->name ?? '-',
                            $q->customer?->name ?? $q->recipient_name,
                            Carbon::parse($q->date)->format('Y-m-d'),
                            $q->expiry_date ? Carbon::parse($q->expiry_date)->format('Y-m-d') : '-',
                            number_format((float) $q->total_amount, 2, '.', ''),
                            strtoupper($q->status),
                        ]);
                    }
                });
            } elseif ($tab === 'orders') {
                fputcsv($handle, ['No. Pesanan SO', 'Bisnis / Tenant', 'Klien', 'Ref Penawaran', 'Tgl Pesanan', 'Nilai Total (IDR)', 'Status']);
                SalesOrder::with(['business', 'customer', 'quotation'])->orderBy('order_date', 'desc')->chunk(200, function ($rows) use ($handle): void {
                    foreach ($rows as $o) {
                        fputcsv($handle, [
                            $o->so_number,
                            $o->business?->name ?? '-',
                            $o->customer?->name ?? $o->recipient_name,
                            $o->quotation?->quotation_number ?? '-',
                            Carbon::parse($o->order_date)->format('Y-m-d'),
                            number_format((float) $o->total_amount, 2, '.', ''),
                            strtoupper($o->status),
                        ]);
                    }
                });
            } elseif ($tab === 'invoices') {
                fputcsv($handle, ['No. Faktur', 'Bisnis / Tenant', 'Klien', 'Tgl Faktur', 'Jatuh Tempo', 'Total Tagihan', 'Terbayar', 'Sisa Piutang', 'Status']);
                Invoice::with(['business', 'customer'])->orderBy('invoice_date', 'desc')->chunk(200, function ($rows) use ($handle): void {
                    foreach ($rows as $i) {
                        fputcsv($handle, [
                            $i->invoice_number,
                            $i->business?->name ?? '-',
                            $i->customer?->name ?? $i->recipient_name,
                            Carbon::parse($i->invoice_date)->format('Y-m-d'),
                            Carbon::parse($i->due_date)->format('Y-m-d'),
                            number_format((float) $i->total_amount, 2, '.', ''),
                            number_format((float) $i->paid_amount, 2, '.', ''),
                            number_format((float) $i->balance_due, 2, '.', ''),
                            strtoupper($i->status),
                        ]);
                    }
                });
            } else {
                fputcsv($handle, ['No. Retur', 'Bisnis / Tenant', 'Klien', 'Faktur Asli', 'Tgl Retur', 'Alasan', 'Metode Refund', 'Nilai Retur', 'Status']);
                SalesReturn::with(['business', 'customer', 'invoice'])->orderBy('return_date', 'desc')->chunk(200, function ($rows) use ($handle): void {
                    foreach ($rows as $r) {
                        fputcsv($handle, [
                            $r->return_number,
                            $r->business?->name ?? '-',
                            $r->customer?->name ?? $r->recipient_name,
                            $r->invoice?->invoice_number ?? '-',
                            Carbon::parse($r->return_date)->format('Y-m-d'),
                            $r->reason ?? '-',
                            strtoupper(str_replace('_', ' ', $r->refund_method)),
                            number_format((float) $r->total_amount, 2, '.', ''),
                            strtoupper($r->status),
                        ]);
                    }
                });
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Provide JSON master data for active business (dynamic modals).
     */
    public function businessResources(Request $request): JsonResponse
    {
        $businessId = $request->query('business_id');
        if (!$businessId) {
            return response()->json([
                'customers' => [],
                'products' => [],
                'locations' => [],
            ]);
        }

        $customers = Customer::where('business_id', $businessId)->select('id', 'name', 'company_name', 'phone')->orderBy('name')->get();
        $products = Product::where('business_id', $businessId)->where('is_active', true)->select('id', 'name', 'code', 'selling_price', 'output_unit_id')->orderBy('name')->get();
        $locations = Location::where('business_id', $businessId)->where('is_active', true)->select('id', 'name')->orderBy('name')->get();

        return response()->json([
            'customers' => $customers,
            'products'  => $products,
            'locations' => $locations,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1. QUOTATIONS CRUD
    // ─────────────────────────────────────────────────────────────────────────

    public function storeQuotation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'business_id'             => ['required', 'uuid', 'exists:businesses,id'],
            'customer_id'             => ['required', 'uuid', 'exists:customers,id'],
            'quotation_number'        => ['nullable', 'string', 'max:64'],
            'date'                    => ['required', 'date'],
            'expiry_date'             => ['nullable', 'date', 'after_or_equal:date'],
            'sender_name'             => ['nullable', 'string', 'max:150'],
            'recipient_name'          => ['nullable', 'string', 'max:150'],
            'discount_amount'         => ['nullable', 'numeric', 'min:0'],
            'tax_percentage'          => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes'                   => ['nullable', 'string', 'max:1000'],
            'items'                   => ['required', 'array', 'min:1'],
            'items.*.product_id'      => ['nullable', 'uuid'],
            'items.*.product_name'    => ['required', 'string', 'max:255'],
            'items.*.unit_price'      => ['required', 'numeric', 'min:0'],
            'items.*.quantity'        => ['required', 'numeric', 'min:0.01'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $business = Business::findOrFail($validated['business_id']);
        $validated['attachment_info'] = '-';
        $validated['created_by'] = null;

        $quotation = $this->pipelineService->createQuotation($business, $validated);

        return redirect()->route('admin.b2b.index', ['tab' => 'quotations', 'business_id' => $business->id])
            ->with('success', "Surat Penawaran {$quotation->quotation_number} berhasil diterbitkan via Admin Console.");
    }

    public function updateQuotation(Request $request, Quotation $quotation): RedirectResponse
    {
        $validated = $request->validate([
            'status'         => ['required', 'string', 'in:draft,sent,accepted,rejected,cancelled'],
            'date'           => ['required', 'date'],
            'expiry_date'    => ['nullable', 'date'],
            'sender_name'    => ['nullable', 'string', 'max:150'],
            'recipient_name' => ['nullable', 'string', 'max:150'],
            'notes'          => ['nullable', 'string', 'max:1000'],
        ]);

        $quotation->update($validated);

        return redirect()->route('admin.b2b.index', ['tab' => 'quotations', 'business_id' => $quotation->business_id])
            ->with('success', "Surat Penawaran {$quotation->quotation_number} berhasil diperbarui.");
    }

    public function destroyQuotation(Quotation $quotation): RedirectResponse
    {
        if ($quotation->salesOrder()->exists()) {
            return back()->with('error', "Surat Penawaran {$quotation->quotation_number} telah dikonversi menjadi Pesanan Penjualan dan tidak dapat dihapus.");
        }

        $num = $quotation->quotation_number;
        $businessId = $quotation->business_id;
        $quotation->items()->delete();
        $quotation->delete();

        return redirect()->route('admin.b2b.index', ['tab' => 'quotations', 'business_id' => $businessId])
            ->with('success', "Surat Penawaran {$num} berhasil dihapus.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. SALES ORDERS CRUD
    // ─────────────────────────────────────────────────────────────────────────

    public function storeSalesOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'business_id'             => ['required', 'uuid', 'exists:businesses,id'],
            'customer_id'             => ['required', 'uuid', 'exists:customers,id'],
            'so_number'               => ['nullable', 'string', 'max:64'],
            'order_date'              => ['required', 'date'],
            'expected_delivery_date'  => ['nullable', 'date', 'after_or_equal:order_date'],
            'shipping_address'        => ['nullable', 'string', 'max:500'],
            'sender_name'             => ['nullable', 'string', 'max:150'],
            'recipient_name'          => ['nullable', 'string', 'max:150'],
            'discount_amount'         => ['nullable', 'numeric', 'min:0'],
            'tax_percentage'          => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes'                   => ['nullable', 'string', 'max:1000'],
            'items'                   => ['required', 'array', 'min:1'],
            'items.*.product_id'      => ['nullable', 'uuid'],
            'items.*.product_name'    => ['required', 'string', 'max:255'],
            'items.*.unit_price'      => ['required', 'numeric', 'min:0'],
            'items.*.quantity'        => ['required', 'numeric', 'min:0.01'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $business = Business::findOrFail($validated['business_id']);
        $validated['attachment_info'] = '-';
        $validated['created_by'] = null;

        $order = $this->pipelineService->createSalesOrder($business, $validated);

        return redirect()->route('admin.b2b.index', ['tab' => 'orders', 'business_id' => $business->id])
            ->with('success', "Pesanan Penjualan {$order->so_number} berhasil dibuat via Admin Console.");
    }

    public function updateSalesOrder(Request $request, SalesOrder $salesOrder): RedirectResponse
    {
        $validated = $request->validate([
            'status'                 => ['required', 'string', 'in:draft,confirmed,partially_fulfilled,fulfilled,cancelled'],
            'expected_delivery_date' => ['nullable', 'date'],
            'shipping_address'       => ['nullable', 'string', 'max:500'],
            'notes'                  => ['nullable', 'string', 'max:1000'],
        ]);

        $salesOrder->update($validated);

        return redirect()->route('admin.b2b.index', ['tab' => 'orders', 'business_id' => $salesOrder->business_id])
            ->with('success', "Pesanan Penjualan {$salesOrder->so_number} berhasil diperbarui.");
    }

    public function destroySalesOrder(SalesOrder $salesOrder): RedirectResponse
    {
        if ($salesOrder->invoices()->where('status', '!=', Invoice::STATUS_VOID)->exists()) {
            return back()->with('error', "Pesanan Penjualan {$salesOrder->so_number} telah memiliki Faktur aktif dan tidak dapat dihapus.");
        }

        $num = $salesOrder->so_number;
        $businessId = $salesOrder->business_id;
        $salesOrder->items()->delete();
        $salesOrder->delete();

        return redirect()->route('admin.b2b.index', ['tab' => 'orders', 'business_id' => $businessId])
            ->with('success', "Pesanan Penjualan {$num} berhasil dihapus.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. INVOICES CRUD
    // ─────────────────────────────────────────────────────────────────────────

    public function storeInvoice(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'business_id'            => ['required', 'uuid', 'exists:businesses,id'],
            'customer_id'            => ['required', 'uuid', 'exists:customers,id'],
            'invoice_number'         => ['nullable', 'string', 'max:64'],
            'invoice_date'           => ['required', 'date'],
            'due_date'               => ['required', 'date', 'after_or_equal:invoice_date'],
            'payment_terms'          => ['nullable', 'string', 'max:50'],
            'sender_name'            => ['nullable', 'string', 'max:150'],
            'recipient_name'         => ['nullable', 'string', 'max:150'],
            'discount_amount'        => ['nullable', 'numeric', 'min:0'],
            'tax_percentage'         => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes'                  => ['nullable', 'string', 'max:1000'],
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.product_id'     => ['nullable', 'uuid'],
            'items.*.item_name'      => ['required', 'string', 'max:255'],
            'items.*.quantity'       => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'     => ['required', 'numeric', 'min:0'],
            'items.*.unit_id'        => ['nullable', 'uuid'],
        ]);

        $business = Business::findOrFail($validated['business_id']);

        $invNumber = $validated['invoice_number'] ?? null;
        if (!$invNumber) {
            $prefix = 'INV-' . date('Ym') . '-';
            $latest = Invoice::where('business_id', $business->id)
                ->where('invoice_number', 'LIKE', $prefix . '%')
                ->orderByDesc('invoice_number')
                ->value('invoice_number');
            $seq = 1;
            if ($latest && preg_match('/-(\d+)$/', $latest, $m)) {
                $seq = ((int) $m[1]) + 1;
            }
            $invNumber = $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        }

        $subtotal = 0.0;
        foreach ($validated['items'] as $item) {
            $subtotal += ((float) $item['quantity'] * (float) $item['unit_price']);
        }

        $discount = (float) ($validated['discount_amount'] ?? 0);
        $taxPercentage = (float) ($validated['tax_percentage'] ?? 0);
        $taxable = max(0.0, $subtotal - $discount);
        $taxAmount = ($taxPercentage / 100.0) * $taxable;
        $total = $taxable + $taxAmount;

        $invoice = Invoice::create([
            'business_id'         => $business->id,
            'customer_id'         => $validated['customer_id'],
            'invoice_number'      => $invNumber,
            'invoice_date'        => $validated['invoice_date'],
            'due_date'            => $validated['due_date'],
            'payment_terms'       => $validated['payment_terms'] ?? 'Net 30',
            'sender_name'         => $validated['sender_name'] ?? $business->name,
            'recipient_name'      => $validated['recipient_name'] ?? null,
            'attachment_info'     => '-',
            'subtotal'            => $subtotal,
            'discount_type'       => 'fixed',
            'discount_value'      => $discount,
            'discount_amount'     => $discount,
            'tax_percentage'      => $taxPercentage,
            'tax_amount'          => $taxAmount,
            'total_amount'        => $total,
            'paid_amount'         => 0,
            'balance_due'         => $total,
            'total_hpp_cost'      => 0,
            'total_gross_profit'  => $taxable,
            'status'              => Invoice::STATUS_UNPAID,
            'notes'               => $validated['notes'] ?? null,
            'created_by'          => null,
        ]);

        $defaultUnitId = Unit::where('business_id', $business->id)->first()?->id;

        foreach ($validated['items'] as $item) {
            $qty = (float) $item['quantity'];
            $price = (float) $item['unit_price'];
            $lineSubtotal = $qty * $price;

            $product = !empty($item['product_id']) ? Product::find($item['product_id']) : null;
            $unitId = $item['unit_id'] ?? $product?->output_unit_id ?? $defaultUnitId;

            InvoiceItem::create([
                'invoice_id'   => $invoice->id,
                'product_id'   => $product?->id,
                'unit_id'      => $unitId,
                'item_name'    => $item['item_name'],
                'unit_price'   => $price,
                'quantity'     => $qty,
                'unit_hpp'     => $product ? (float) $product->base_cost : 0.0,
                'subtotal'     => $lineSubtotal,
                'total_hpp'    => $product ? ((float) $product->base_cost * $qty) : 0.0,
                'gross_profit' => $lineSubtotal - ($product ? ((float) $product->base_cost * $qty) : 0.0),
            ]);
        }

        $invoice->recalculateTotals();

        return redirect()->route('admin.b2b.index', ['tab' => 'invoices', 'business_id' => $business->id])
            ->with('success', "Faktur Komersial {$invoice->invoice_number} berhasil diterbitkan via Admin Console.");
    }

    public function updateInvoice(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'due_date'      => ['required', 'date'],
            'payment_terms' => ['nullable', 'string', 'max:50'],
            'notes'         => ['nullable', 'string', 'max:1000'],
        ]);

        $invoice->update($validated);

        return redirect()->route('admin.b2b.index', ['tab' => 'invoices', 'business_id' => $invoice->business_id])
            ->with('success', "Faktur {$invoice->invoice_number} berhasil diperbarui.");
    }

    public function voidInvoice(Invoice $invoice): RedirectResponse
    {
        if ($invoice->paid_amount > 0) {
            return back()->with('error', "Faktur {$invoice->invoice_number} telah memiliki pembayaran tercatat dan tidak dapat dibatalkan (void).");
        }

        $invoice->update(['status' => Invoice::STATUS_VOID]);

        return redirect()->route('admin.b2b.index', ['tab' => 'invoices', 'business_id' => $invoice->business_id])
            ->with('success', "Faktur {$invoice->invoice_number} berhasil dibatalkan (void).");
    }

    public function destroyInvoice(Invoice $invoice): RedirectResponse
    {
        if ($invoice->paid_amount > 0) {
            return back()->with('error', "Faktur {$invoice->invoice_number} memiliki transaksi pembayaran dan tidak dapat dihapus.");
        }

        $num = $invoice->invoice_number;
        $businessId = $invoice->business_id;
        $invoice->items()->delete();
        $invoice->delete();

        return redirect()->route('admin.b2b.index', ['tab' => 'invoices', 'business_id' => $businessId])
            ->with('success', "Faktur {$num} berhasil dihapus.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4. SALES RETURNS CRUD
    // ─────────────────────────────────────────────────────────────────────────

    public function storeSalesReturn(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'business_id'          => ['required', 'uuid', 'exists:businesses,id'],
            'customer_id'          => ['required', 'uuid', 'exists:customers,id'],
            'invoice_id'           => ['nullable', 'uuid', 'exists:invoices,id'],
            'location_id'          => ['nullable', 'uuid', 'exists:locations,id'],
            'return_number'        => ['nullable', 'string', 'max:64'],
            'return_date'          => ['required', 'date'],
            'reason'               => ['required', 'string', 'max:500'],
            'refund_method'        => ['required', 'string', 'in:credit_note,cash,bank_transfer'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.product_id'   => ['nullable', 'uuid'],
            'items.*.item_name'    => ['required', 'string', 'max:255'],
            'items.*.quantity'     => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'   => ['required', 'numeric', 'min:0'],
            'items.*.reason'       => ['nullable', 'string', 'max:255'],
        ]);

        $business = Business::findOrFail($validated['business_id']);

        $retNumber = $validated['return_number'] ?? null;
        if (!$retNumber) {
            $prefix = 'RET-' . date('Ym') . '-';
            $latest = SalesReturn::where('business_id', $business->id)
                ->where('return_number', 'LIKE', $prefix . '%')
                ->orderByDesc('return_number')
                ->value('return_number');
            $seq = 1;
            if ($latest && preg_match('/-(\d+)$/', $latest, $m)) {
                $seq = ((int) $m[1]) + 1;
            }
            $retNumber = $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        }

        $totalAmount = 0.0;
        foreach ($validated['items'] as $item) {
            $totalAmount += ((float) $item['quantity'] * (float) $item['unit_price']);
        }

        $locationId = $validated['location_id'] ?? Location::where('business_id', $business->id)->value('id');

        $salesReturn = SalesReturn::create([
            'business_id'   => $business->id,
            'customer_id'   => $validated['customer_id'],
            'invoice_id'    => $validated['invoice_id'] ?? null,
            'location_id'   => $locationId,
            'return_number' => $retNumber,
            'return_date'   => $validated['return_date'],
            'reason'        => $validated['reason'],
            'total_amount'  => $totalAmount,
            'status'        => SalesReturn::STATUS_DRAFT,
            'refund_method' => $validated['refund_method'],
            'created_by'    => null,
        ]);

        foreach ($validated['items'] as $item) {
            $qty = (float) $item['quantity'];
            $price = (float) $item['unit_price'];

            SalesReturnItem::create([
                'sales_return_id' => $salesReturn->id,
                'product_id'      => $item['product_id'] ?? null,
                'item_name'       => $item['item_name'],
                'quantity'        => $qty,
                'unit_price'      => $price,
                'subtotal'        => $qty * $price,
                'reason'          => $item['reason'] ?? $validated['reason'],
            ]);
        }

        return redirect()->route('admin.b2b.index', ['tab' => 'returns', 'business_id' => $business->id])
            ->with('success', "Klaim Retur Penjualan {$salesReturn->return_number} berhasil dibuat via Admin Console.");
    }

    public function updateSalesReturn(Request $request, SalesReturn $salesReturn): RedirectResponse
    {
        $validated = $request->validate([
            'reason'        => ['required', 'string', 'max:500'],
            'refund_method' => ['required', 'string', 'in:credit_note,cash,bank_transfer'],
        ]);

        $salesReturn->update($validated);

        return redirect()->route('admin.b2b.index', ['tab' => 'returns', 'business_id' => $salesReturn->business_id])
            ->with('success', "Retur Penjualan {$salesReturn->return_number} berhasil diperbarui.");
    }

    public function rejectSalesReturn(Request $request, SalesReturn $salesReturn): RedirectResponse
    {
        if ($salesReturn->status === SalesReturn::STATUS_COMPLETED) {
            return back()->with('error', "Retur yang telah selesai dan direstock tidak dapat ditolak.");
        }

        $reason = $request->input('rejection_reason', 'Ditolak oleh Administrator');
        $salesReturn->update([
            'status' => 'rejected',
            'reason' => trim("{$salesReturn->reason} [Penolakan: {$reason}]"),
        ]);

        return redirect()->route('admin.b2b.index', ['tab' => 'returns', 'business_id' => $salesReturn->business_id])
            ->with('success', "Retur Penjualan {$salesReturn->return_number} ditolak.");
    }

    public function destroySalesReturn(SalesReturn $salesReturn): RedirectResponse
    {
        if ($salesReturn->status === SalesReturn::STATUS_COMPLETED) {
            return back()->with('error', "Retur yang telah selesai dan direstock tidak dapat dihapus.");
        }

        $num = $salesReturn->return_number;
        $businessId = $salesReturn->business_id;
        $salesReturn->items()->delete();
        $salesReturn->delete();

        return redirect()->route('admin.b2b.index', ['tab' => 'returns', 'business_id' => $businessId])
            ->with('success', "Retur Penjualan {$num} berhasil dihapus.");
    }
}
