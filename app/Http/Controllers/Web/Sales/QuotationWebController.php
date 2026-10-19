<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Sales;

use App\Domain\Sales\SalesPipelineService;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class QuotationWebController extends Controller
{
    public function __construct(
        private readonly SalesPipelineService $salesPipelineService = new SalesPipelineService
    ) {}

    public function index(Request $request): View
    {
        $business = Context::requireBusiness();

        $query = Quotation::with('customer')
            ->where('business_id', $business->id)
            ->latest('date');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('quotation_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('customer', fn($c) => $c->where('name', 'LIKE', "%{$search}%"));
            });
        }

        $quotations = $query->paginate(15)->withQueryString();

        return view('app.quotations.index', compact('business', 'quotations'));
    }

    public function create(): View
    {
        $business        = Context::requireBusiness();
        $customers       = Customer::where('business_id', $business->id)->orderBy('name')->get();
        $products        = Product::where('business_id', $business->id)->where('is_active', true)->orderBy('name')->get();
        $goodsProducts   = Product::where('business_id', $business->id)->where('is_active', true)->goods()->orderBy('name')->get();
        $serviceProducts = Product::where('business_id', $business->id)->where('is_active', true)->services()->orderBy('name')->get();
        $nextNumber      = $this->salesPipelineService->generateQuotationNumber($business);

        return view('app.quotations.create', compact('business', 'customers', 'products', 'goodsProducts', 'serviceProducts', 'nextNumber'));
    }

    public function store(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'quotation_number' => ['nullable', 'string', 'max:64'],
            'date' => ['required', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:date'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.product_name' => ['required_without:items.*.product_id', 'nullable', 'string'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $quotation = $this->salesPipelineService->createQuotation($business, $validated);

        return redirect()->route('sales.quotations.show', $quotation)
            ->with('success', "Surat Penawaran {$quotation->quotation_number} berhasil diterbitkan.");
    }

    public function show(Quotation $quotation): View
    {
        $business = Context::requireBusiness();
        abort_unless($quotation->business_id === $business->id, 403);

        $quotation->load('customer', 'items.product', 'salesOrder');

        return view('app.quotations.show', compact('business', 'quotation'));
    }

    public function convertToSalesOrder(Quotation $quotation): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($quotation->business_id === $business->id, 403);

        if ($quotation->salesOrder) {
            return redirect()->route('sales.orders.show', $quotation->salesOrder)
                ->with('info', "Penawaran ini sudah dikonversi ke Pesanan {$quotation->salesOrder->so_number}.");
        }

        $salesOrder = $this->salesPipelineService->convertQuotationToSalesOrder($quotation);

        return redirect()->route('sales.orders.show', $salesOrder)
            ->with('success', "Penawaran {$quotation->quotation_number} berhasil diubah menjadi Pesanan Penjualan {$salesOrder->so_number}!");
    }

    public function edit(Quotation $quotation): View
    {
        $business        = Context::requireBusiness();
        abort_unless($quotation->business_id === $business->id, 403);

        $quotation->load('customer', 'items.product');
        $customers       = Customer::where('business_id', $business->id)->orderBy('name')->get();
        $products        = Product::where('business_id', $business->id)->where('is_active', true)->orderBy('name')->get();
        $goodsProducts   = Product::where('business_id', $business->id)->where('is_active', true)->goods()->orderBy('name')->get();
        $serviceProducts = Product::where('business_id', $business->id)->where('is_active', true)->services()->orderBy('name')->get();

        // Ensure any product from existing items is included in the list
        $existingProductIds = $quotation->items->pluck('product_id')->filter()->unique();
        if ($existingProductIds->isNotEmpty()) {
            $missingProducts = Product::where('business_id', $business->id)->whereIn('id', $existingProductIds)->get();
            $products = $products->merge($missingProducts)->unique('id')->values();
            $goodsProducts = $goodsProducts->merge($missingProducts->filter(fn($p) => !$p->isService()))->unique('id')->values();
            $serviceProducts = $serviceProducts->merge($missingProducts->filter(fn($p) => $p->isService()))->unique('id')->values();
        }

        return view('app.quotations.edit', compact('business', 'quotation', 'customers', 'products', 'goodsProducts', 'serviceProducts'));
    }

    public function update(Request $request, Quotation $quotation): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($quotation->business_id === $business->id, 403);

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'quotation_number' => ['nullable', 'string', 'max:64'],
            'date' => ['required', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:date'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'in:draft,sent,accepted,declined,converted'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'terms_and_conditions' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.product_name' => ['required_without:items.*.product_id', 'nullable', 'string'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        $updated = $this->salesPipelineService->updateQuotation($quotation, $validated);

        return redirect()->route('sales.quotations.show', $updated)
            ->with('success', "Surat Penawaran {$updated->quotation_number} berhasil diperbarui.");
    }

    public function pdf(Request $request, Quotation $quotation): \Illuminate\Http\Response
    {
        $business = Context::requireBusiness();
        abort_unless($quotation->business_id === $business->id, 403);

        $quotation->load(['customer', 'items.product', 'salesOrder']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.quotation_pdf', [
            'business' => $business,
            'quotation' => $quotation,
        ])->setPaper('a4', 'portrait');

        $filename = 'Penawaran-' . \Illuminate\Support\Str::slug($quotation->quotation_number) . '.pdf';

        if ($request->query('download') === '1') {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }

    public function print(Request $request, Quotation $quotation): \Illuminate\Http\Response
    {
        return $this->pdf($request, $quotation);
    }
}
