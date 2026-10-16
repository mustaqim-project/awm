<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

final class SupplierWebController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('require.permission:master_data.suppliers.view', only: ['index', 'export', 'downloadTemplate']),
            new Middleware(['require.permission:master_data.suppliers.manage', 'entitlement:supplier'], only: ['store', 'executeImport']),
            new Middleware('require.permission:master_data.suppliers.manage', only: ['update', 'destroy', 'previewImport']),
        ];
    }
    /**
     * Display a listing of suppliers with search and material counts.
     */
    public function index(Request $request): View
    {
        $business = Context::requireBusiness();

        $query = Supplier::withCount('materials')
            ->latest();

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->paginate(15)->withQueryString();

        return view('app.suppliers.index', compact('business', 'suppliers'));
    }

    /**
     * Store a new supplier (via regular form or AJAX modal).
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:500'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:100'],
            'bank_account_holder' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $supplier = Supplier::create([
            'business_id' => $business->id,
            'name' => $validated['name'],
            'contact_person' => $validated['contact_person'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'bank_name' => $validated['bank_name'] ?? null,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'bank_account_holder' => $validated['bank_account_holder'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('purchasing.supplier.messages.created_success'),
                'supplier' => $supplier,
            ], 201);
        }

        return back()->with('success', __('purchasing.supplier.messages.created_success'));
    }

    /**
     * Update an existing supplier.
     */
    public function update(Request $request, Supplier $supplier): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        abort_unless($supplier->business_id === $business->id, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:500'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:100'],
            'bank_account_holder' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $supplier->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('purchasing.supplier.messages.updated_success', ['name' => $supplier->name]),
                'supplier' => $supplier,
            ]);
        }

        return redirect()->route('suppliers.index')->with('success', __('purchasing.supplier.messages.updated_success', ['name' => $supplier->name]));
    }

    /**
     * Delete a supplier.
     */
    public function destroy(Supplier $supplier): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        abort_unless($supplier->business_id === $business->id, 403);

        // Deletion Guard: Check active transactions (Invoices, POs, Goods Receipts)
        $hasInvoices = $supplier->invoices()->exists();
        $hasPurchaseOrders = method_exists($supplier, 'purchaseOrders') && $supplier->purchaseOrders()->exists();
        $hasGoodsReceipts = $supplier->goodsReceipts()->exists();

        if ($hasInvoices || $hasPurchaseOrders || $hasGoodsReceipts) {
            $msg = __('purchasing.supplier.messages.cannot_delete_has_po');

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 422);
            }

            return back()->with('error', $msg);
        }

        $supplier->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('purchasing.supplier.messages.deleted_success'),
            ]);
        }

        return back()->with('success', __('purchasing.supplier.messages.deleted_success'));
    }

    /**
     * Export suppliers to Excel.
     */
    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $business = Context::requireBusiness();

        $query = Supplier::where('business_id', $business->id)->latest();
        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return app(\App\Domain\Import\DataExchangeService::class)->exportSuppliers($business, $query);
    }

    /**
     * Download Excel template for Supplier import.
     */
    public function downloadTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $business = Context::requireBusiness();
        return app(\App\Domain\Import\DataExchangeService::class)->generateSupplierTemplate($business);
    }

    /**
     * Preview uploaded supplier Excel file.
     */
    public function previewImport(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $request->validate([
            'file' => ['required', 'file', 'max:5120', 'mimes:xlsx,xls,csv,txt'],
        ]);

        $preview = app(\App\Domain\Import\DataExchangeService::class)->parseAndPreviewSuppliers($business, $request->file('file'));

        return response()->json([
            'success' => true,
            'data' => $preview,
        ]);
    }

    /**
     * Execute confirmed supplier import.
     */
    public function executeImport(Request $request): JsonResponse|RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'rows' => ['required'],
            'duplicate_strategy' => ['required', 'in:skip,update'],
        ]);

        $rows = is_string($validated['rows']) ? json_decode($validated['rows'], true) : $validated['rows'];
        if (!is_array($rows) || empty($rows)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada data supplier yang valid untuk diimpor.'], 422);
        }

        $result = app(\App\Domain\Import\DataExchangeService::class)->executeSupplierImport($business, $rows, $validated['duplicate_strategy']);

        $message = "Import Supplier Selesai: {$result['imported']} supplier baru berhasil ditambahkan";
        if ($result['updated'] > 0) {
            $message .= ", {$result['updated']} supplier diperbarui";
        }
        if ($result['skipped'] > 0) {
            $message .= ", {$result['skipped']} duplikat/error dilewati";
        }
        $message .= '.';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $result,
                'result' => $result,
                'redirect_url' => route('suppliers.index'),
            ]);
        }

        return redirect()->route('suppliers.index')->with('success', $message);
    }
}
