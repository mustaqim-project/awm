<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Manufacturing\ProductionService;
use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Support\Context;
use App\Support\PaginationHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class ProductionWebController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('require.permission:production.view', only: ['index', 'show', 'print']),
            new Middleware('require.permission:production.create', only: ['create', 'store']),
            new Middleware('require.permission:production.edit,production.create', only: ['edit', 'update']),
            new Middleware('require.permission:production.execute', only: ['issueMaterials']),
            new Middleware('require.permission:production.complete', only: ['complete']),
            new Middleware('require.permission:production.cancel', only: ['cancel']),
        ];
    }

    public function __construct(
        private readonly ProductionService $productionService = new ProductionService
    ) {}

    /**
     * Display list of Production Orders (SPKP) with Bento KPIs.
     */
    public function index(Request $request): View
    {
        $business = Context::requireBusiness();

        $query = ProductionOrder::where('business_id', $business->id)
            ->with(['product.outputUnit', 'sourceLocation', 'targetLocation'])
            ->latest();

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->get('status'));
        }

        $perPage = PaginationHelper::perPage($request, 20);
        $orders = $query->paginate($perPage)->withQueryString();

        // Calculate Bento KPIs
        $totalOrdersCount = ProductionOrder::where('business_id', $business->id)->count();
        $inProgressCount = ProductionOrder::where('business_id', $business->id)
            ->whereIn('status', [ProductionOrder::STATUS_RELEASED, ProductionOrder::STATUS_IN_PROGRESS])
            ->count();
        $completedThisMonth = ProductionOrder::where('business_id', $business->id)
            ->where('status', ProductionOrder::STATUS_COMPLETED)
            ->whereYear('actual_end_date', now()->year)
            ->whereMonth('actual_end_date', now()->month)
            ->count();

        $avgYield = (float) (ProductionOrder::where('business_id', $business->id)
            ->where('status', ProductionOrder::STATUS_COMPLETED)
            ->where('planned_quantity', '>', 0)
            ->selectRaw('AVG((actual_quantity / planned_quantity) * 100) as avg_yield')
            ->value('avg_yield') ?? 100.0);

        return view('app.production.index', compact(
            'business',
            'orders',
            'totalOrdersCount',
            'inProgressCount',
            'completedThisMonth',
            'avgYield'
        ));
    }

    /**
     * Show form to create a new Production Order.
     */
    public function create(Request $request): View
    {
        $business = Context::requireBusiness();

        // Fetch products that have active BOM / recipes or are goods
        $products = Product::where('business_id', $business->id)
            ->where('is_active', true)
            ->where('type', Product::TYPE_GOODS)
            ->with(['activeCostModel.bomHeaders.items.material.unit', 'outputUnit'])
            ->orderBy('name')
            ->get();

        $locations = Location::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $preselectedProductId = $request->get('product_id');

        return view('app.production.create', compact('business', 'products', 'locations', 'preselectedProductId'));
    }

    /**
     * Store new Production Order and generate initial material requirements.
     */
    public function store(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'product_id' => [
                'required',
                'string',
                Rule::exists('products', 'id')->where('business_id', $business->id),
            ],
            'planned_quantity' => ['required', 'numeric', 'gt:0'],
            'source_location_id' => [
                'required',
                'string',
                Rule::exists('locations', 'id')->where('business_id', $business->id),
            ],
            'target_location_id' => [
                'required',
                'string',
                Rule::exists('locations', 'id')->where('business_id', $business->id),
            ],
            'planned_start_date' => ['required', 'date'],
            'total_labor_cost' => ['nullable', 'numeric', 'gte:0'],
            'total_machine_cost' => ['nullable', 'numeric', 'gte:0'],
            'batch_number' => ['nullable', 'string', 'max:100'],
            'expiry_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $order = $this->productionService->createOrder($business, $validated, $request->user());

        return redirect()->route('production.show', $order)->with('success', "Perintah Produksi #{$order->order_number} berhasil dibuat dalam status Draf.");
    }

    /**
     * Display Production Order details, BOM requirements, actual material usage, and actions.
     */
    public function show(ProductionOrder $productionOrder): View
    {
        $business = Context::requireBusiness();
        abort_unless($productionOrder->business_id === $business->id, 404);

        $productionOrder->load([
            'product.outputUnit',
            'bomHeader.items.material.unit',
            'sourceLocation',
            'targetLocation',
            'creator',
            'completer',
            'items.material.unit',
            'items.unit',
        ]);

        return view('app.production.show', compact('business', 'productionOrder'));
    }

    /**
     * Show form to edit an existing Production Order.
     */
    public function edit(ProductionOrder $productionOrder): View|RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($productionOrder->business_id === $business->id, 404);

        if ($productionOrder->isCompleted() || $productionOrder->isCancelled()) {
            return redirect()->route('production.show', $productionOrder)
                ->with('error', __('production.cannot_edit_completed_or_cancelled', ['status' => $productionOrder->status]));
        }

        $productionOrder->load([
            'product.outputUnit',
            'bomHeader.items.material.unit',
            'sourceLocation',
            'targetLocation',
            'items.material.unit',
            'items.unit',
        ]);

        $products = Product::where('business_id', $business->id)
            ->where('is_active', true)
            ->where('type', Product::TYPE_GOODS)
            ->with(['activeCostModel.bomHeaders.items.material.unit', 'outputUnit'])
            ->orderBy('name')
            ->get();

        $locations = Location::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('app.production.edit', compact('business', 'productionOrder', 'products', 'locations'));
    }

    /**
     * Update an existing Production Order.
     */
    public function update(Request $request, ProductionOrder $productionOrder): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($productionOrder->business_id === $business->id, 404);

        if ($productionOrder->isCompleted() || $productionOrder->isCancelled()) {
            return redirect()->route('production.show', $productionOrder)
                ->with('error', __('production.cannot_edit_completed_or_cancelled', ['status' => $productionOrder->status]));
        }

        $rules = [
            'planned_start_date' => ['nullable', 'date'],
            'total_labor_cost' => ['nullable', 'numeric', 'gte:0'],
            'total_machine_cost' => ['nullable', 'numeric', 'gte:0'],
            'batch_number' => ['nullable', 'string', 'max:100'],
            'expiry_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['nullable', 'array'],
            'items.*.id' => ['required_with:items', 'string'],
        ];

        if ($productionOrder->isDraft()) {
            $rules['product_id'] = [
                'nullable',
                'string',
                Rule::exists('products', 'id')->where('business_id', $business->id),
            ];
            $rules['planned_quantity'] = ['required', 'numeric', 'gt:0'];
            $rules['source_location_id'] = [
                'required',
                'string',
                Rule::exists('locations', 'id')->where('business_id', $business->id),
            ];
            $rules['target_location_id'] = [
                'required',
                'string',
                Rule::exists('locations', 'id')->where('business_id', $business->id),
            ];
            $rules['items.*.planned_quantity'] = ['nullable', 'numeric', 'gte:0'];
        } else {
            $rules['target_location_id'] = [
                'nullable',
                'string',
                Rule::exists('locations', 'id')->where('business_id', $business->id),
            ];
            $rules['items.*.actual_consumed_quantity'] = ['nullable', 'numeric', 'gte:0'];
        }

        $validated = $request->validate($rules);

        $this->productionService->updateOrder($productionOrder, $validated, $request->user());

        return redirect()->route('production.show', $productionOrder)
            ->with('success', __('production.messages_order_updated', ['order_number' => $productionOrder->order_number]));
    }

    /**
     * Render professional production order print-ready layout (A4 / SPKP).
     */
    public function print(ProductionOrder $productionOrder): View
    {
        $business = Context::requireBusiness();
        abort_unless($productionOrder->business_id === $business->id, 404);

        $productionOrder->load([
            'product.outputUnit',
            'bomHeader',
            'sourceLocation',
            'targetLocation',
            'creator',
            'completer',
            'items.material.unit',
            'items.unit',
        ]);

        return view('app.production.print', compact('business', 'productionOrder'));
    }

    /**
     * Issue raw materials from source warehouse to factory floor (WIP).
     */
    public function issueMaterials(Request $request, ProductionOrder $productionOrder): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($productionOrder->business_id === $business->id, 403);

        $this->productionService->issueMaterials($productionOrder, $request->all(), $request->user());

        return back()->with('success', "Bahan baku untuk Perintah Produksi #{$productionOrder->order_number} berhasil dikeluarkan ke lantai produksi.");
    }

    /**
     * Complete production: receive finished goods into target warehouse and finalize costing.
     */
    public function complete(Request $request, ProductionOrder $productionOrder): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($productionOrder->business_id === $business->id, 403);

        $validated = $request->validate([
            'actual_quantity' => ['required', 'numeric', 'gte:0'],
            'rejected_quantity' => ['nullable', 'numeric', 'gte:0'],
            'batch_number' => ['nullable', 'string', 'max:100'],
            'expiry_date' => ['nullable', 'date'],
            'total_labor_cost' => ['nullable', 'numeric', 'gte:0'],
            'total_machine_cost' => ['nullable', 'numeric', 'gte:0'],
            'update_product_base_cost' => ['nullable', 'boolean'],
        ]);

        $this->productionService->completeOrder($productionOrder, $validated, $request->user());

        return back()->with('success', "Hasil produksi berhasil dicatat. Stok barang jadi telah ditambahkan ke gudang #{$productionOrder->targetLocation?->name}.");
    }

    /**
     * Cancel production order and return materials if already issued.
     */
    public function cancel(Request $request, ProductionOrder $productionOrder): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($productionOrder->business_id === $business->id, 403);

        $this->productionService->cancelOrder($productionOrder, $request->user());

        return back()->with('success', "Perintah Produksi #{$productionOrder->order_number} berhasil dibatalkan.");
    }
}
