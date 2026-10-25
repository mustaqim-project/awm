<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class CustomerWebController extends Controller implements HasMiddleware
{
    /**
     * Controller-level RBAC & entitlement middleware declarations.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('require.permission:customers.view', only: ['index', 'export', 'downloadTemplate']),
            new Middleware('require.permission:customers.create', only: ['store', 'previewImport', 'executeImport']),
            new Middleware('require.permission:customers.edit', only: ['update']),
            new Middleware('require.permission:customers.delete', only: ['destroy']),
            new Middleware('entitlement:customer', only: ['store', 'executeImport']),
        ];
    }

    /**
     * Display a unified listing of customers with deep-linking to CRM member & voucher submodules.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $business = Context::requireBusiness();

        if ($request->get('tab') === 'members') {
            return redirect()->route('crm.members.index', $request->except('tab'));
        }

        if ($request->get('tab') === 'vouchers') {
            return redirect()->route('crm.vouchers.index', $request->except('tab'));
        }

        // Commercial & General Customers
        $customersQuery = Customer::where('business_id', $business->id)
            ->withCount(['invoices', 'purchaseOrders', 'posOrders'])
            ->latest();

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $customersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('vehicle_license_plate', 'like', "%{$search}%")
                    ->orWhere('vehicle_model', 'like', "%{$search}%");
            });
        }
        $customers = $customersQuery->paginate(15)->withQueryString();

        // Bento Hero Metrics
        $totalCustomers = Customer::where('business_id', $business->id)->count();
        $totalCorporate = Customer::where('business_id', $business->id)
            ->whereNotNull('company_name')
            ->where('company_name', '!=', '')
            ->count();
        $avgPaymentTerms = (int) round((float) (Customer::where('business_id', $business->id)->avg('payment_terms_days') ?: 30));
        $totalCreditReceivable = (float) Customer::where('business_id', $business->id)->sum('current_credit_balance');
        $totalPointsIssued = (int) Customer::where('business_id', $business->id)->sum('points_balance');

        return view('app.customers.index', compact(
            'business',
            'customers',
            'totalCustomers',
            'totalCorporate',
            'avgPaymentTerms',
            'totalPointsIssued',
            'totalCreditReceivable'
        ));
    }

    /**
     * Store a new customer.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('customers', 'code')->where('business_id', $business->id)],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'vehicle_license_plate' => ['nullable', 'string', 'max:30'],
            'vehicle_model' => ['nullable', 'string', 'max:100'],
            'vehicle_mileage' => ['nullable', 'integer', 'min:0'],
            'billing_address' => ['nullable', 'string', 'max:500'],
            'shipping_address' => ['nullable', 'string', 'max:500'],
            'tax_identification_number' => ['nullable', 'string', 'max:50'],
            'payment_terms_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $customer = Customer::create([
            'business_id' => $business->id,
            'name' => $validated['name'],
            'company_name' => $validated['company_name'] ?? null,
            'code' => $validated['code'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'vehicle_license_plate' => ! empty($validated['vehicle_license_plate']) ? strtoupper(trim($validated['vehicle_license_plate'])) : null,
            'vehicle_model' => $validated['vehicle_model'] ?? null,
            'vehicle_mileage' => isset($validated['vehicle_mileage']) ? (int) $validated['vehicle_mileage'] : null,
            'billing_address' => $validated['billing_address'] ?? null,
            'shipping_address' => $validated['shipping_address'] ?? null,
            'tax_identification_number' => $validated['tax_identification_number'] ?? null,
            'payment_terms_days' => (int) ($validated['payment_terms_days'] ?? 30),
            'notes' => $validated['notes'] ?? null,
            'is_active' => true,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pelanggan berhasil ditambahkan.',
                'customer' => $customer,
            ], 201);
        }

        return redirect()->route('customers.index')->with('success', "Pelanggan '{$customer->name}' berhasil ditambahkan.");
    }

    /**
     * Update an existing customer.
     */
    public function update(Request $request, Customer $customer): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        abort_unless($customer->business_id === $business->id, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('customers', 'code')->where('business_id', $business->id)->ignore($customer->id)],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'vehicle_license_plate' => ['nullable', 'string', 'max:30'],
            'vehicle_model' => ['nullable', 'string', 'max:100'],
            'vehicle_mileage' => ['nullable', 'integer', 'min:0'],
            'billing_address' => ['nullable', 'string', 'max:500'],
            'shipping_address' => ['nullable', 'string', 'max:500'],
            'tax_identification_number' => ['nullable', 'string', 'max:50'],
            'payment_terms_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        if (array_key_exists('vehicle_license_plate', $validated) && ! empty($validated['vehicle_license_plate'])) {
            $validated['vehicle_license_plate'] = strtoupper(trim($validated['vehicle_license_plate']));
        }

        $customer->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data pelanggan berhasil diperbarui.',
                'customer' => $customer,
            ]);
        }

        return redirect()->route('customers.index')->with('success', "Data pelanggan '{$customer->name}' berhasil diperbarui.");
    }

    /**
     * Export customers to Excel.
     */
    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $business = Context::requireBusiness();

        $query = Customer::where('business_id', $business->id)->latest();
        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return app(\App\Domain\Import\DataExchangeService::class)->exportCustomers($business, $query);
    }

    /**
     * Download Excel template for Customer import.
     */
    public function downloadTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $business = Context::requireBusiness();
        return app(\App\Domain\Import\DataExchangeService::class)->generateCustomerTemplate($business);
    }

    /**
     * Preview uploaded customer Excel file.
     */
    public function previewImport(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $request->validate([
            'file' => ['required', 'file', 'max:5120', 'mimes:xlsx,xls,csv,txt'],
        ]);

        $preview = app(\App\Domain\Import\DataExchangeService::class)->parseAndPreviewCustomers($business, $request->file('file'));

        return response()->json([
            'success' => true,
            'data' => $preview,
        ]);
    }

    /**
     * Execute confirmed customer import.
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
            return response()->json(['success' => false, 'message' => 'Tidak ada data pelanggan yang valid untuk diimpor.'], 422);
        }

        $result = app(\App\Domain\Import\DataExchangeService::class)->executeCustomerImport($business, $rows, $validated['duplicate_strategy']);

        $message = "Import Pelanggan Selesai: {$result['imported']} pelanggan baru berhasil ditambahkan";
        if ($result['updated'] > 0) {
            $message .= ", {$result['updated']} pelanggan diperbarui";
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
                'redirect_url' => route('customers.index'),
            ]);
        }

        return redirect()->route('customers.index')->with('success', $message);
    }

    /**
     * Delete a customer.
     */
    public function destroy(Customer $customer): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        abort_unless($customer->business_id === $business->id, 403);

        // Deletion Guard: Check if customer still has outstanding credit balance
        if ((float) ($customer->current_credit_balance ?? 0) > 0) {
            $formattedAmount = number_format((float) $customer->current_credit_balance, 0, ',', '.');
            $msg = "Tidak dapat menghapus pelanggan yang masih memiliki saldo piutang/kasbon berjalan sebesar Rp {$formattedAmount}. Selesaikan pelunasan terlebih dahulu.";

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 422);
            }

            return back()->with('error', $msg);
        }

        $customer->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Pelanggan '{$customer->name}' berhasil dihapus.",
            ]);
        }

        return redirect()->route('customers.index')->with('success', "Pelanggan '{$customer->name}' berhasil dihapus.");
    }
}

