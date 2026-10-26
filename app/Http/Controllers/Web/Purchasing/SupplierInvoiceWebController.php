<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Purchasing;

use App\Domain\Purchasing\SupplierInvoiceService;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CashAccount;
use App\Models\SupplierInvoice;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use InvalidArgumentException;

final class SupplierInvoiceWebController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('require.permission:purchasing.bills', only: ['index', 'show', 'recordPayment']),
        ];
    }

    public function __construct(
        private readonly SupplierInvoiceService $supplierInvoiceService = new SupplierInvoiceService,
    ) {}

    public function index(Request $request): View
    {
        $business = Context::requireBusiness();
        $query = SupplierInvoice::where('business_id', $business->id)->with('supplier')->latest('invoice_date');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        return view('app.purchasing.bills.index', [
            'business' => $business,
            'invoices' => $query->paginate(20)->withQueryString(),
        ]);
    }

    public function show(SupplierInvoice $invoice): View
    {
        $business = Context::requireBusiness();
        abort_unless($invoice->business_id === $business->id, 403);

        $cashAccounts = CashAccount::where('business_id', $business->id)->where('is_active', true)->get();

        return view('app.purchasing.bills.show', [
            'business' => $business,
            'invoice' => $invoice->load(['supplier', 'goodsReceipt', 'purchaseOrder', 'payments']),
            'cashAccounts' => $cashAccounts,
        ]);
    }

    public function recordPayment(Request $request, SupplierInvoice $invoice): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($invoice->business_id === $business->id, 403);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0', 'max:' . ((float) $invoice->balance_due + 0.005)],
            'payment_date' => ['nullable', 'date'],
            'payment_method' => ['required', 'in:cash,bank_transfer,qris'],
            'cash_account_id' => ['nullable', Rule::exists('cash_accounts', 'id')->where('business_id', $business->id)],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'supervisor_pin' => ['nullable', 'string'],
        ]);

        // Proteksi Otorisasi Pengeluaran Kas Bernilai Besar (>= Rp 5.000.000)
        if ((float) $validated['amount'] >= 5000000 && ! empty($business->pos_supervisor_pin)) {
            $pin = $request->input('supervisor_pin') ?? $request->input('pin');
            if (empty($pin) || ! Hash::check((string) $pin, $business->pos_supervisor_pin)) {
                return back()->withInput()->withErrors(['supervisor_pin' => __('purchasing.messages.supervisor_pin_required_for_large_payment')]);
            }
        }

        try {
            $payment = $this->supplierInvoiceService->recordPayment($invoice, array_merge($validated, [
                'created_by' => auth()->id(),
            ]));

            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => auth()->id(),
                'auditable_type' => SupplierInvoice::class,
                'auditable_id' => $invoice->id,
                'action' => 'supplier_invoice.payment_recorded',
                'risk_level' => AuditLog::RISK_MEDIUM,
                'new_values' => [
                    'amount' => $validated['amount'],
                    'payment_method' => $validated['payment_method'],
                    'invoice_number' => $invoice->invoice_number,
                    'supplier_id' => $invoice->supplier_id,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->withErrors(['amount' => $exception->getMessage()]);
        }

        return redirect()->route('purchasing.bills.show', $invoice)
            ->with('success', __('purchasing.messages.payment_success'));
    }
}
