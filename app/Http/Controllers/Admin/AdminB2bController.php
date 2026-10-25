<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Commerce\InvoiceService;
use App\Domain\Commerce\SalesReturnService;
use App\Domain\Sales\SalesPipelineService;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Invoice;
use App\Models\PaymentAccount;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\SalesReturn;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
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
                'index', 'pdfQuotation', 'pdfSalesOrder', 'pdfInvoice', 'pdfSalesReturn',
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
            'paymentAccounts'
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
}
