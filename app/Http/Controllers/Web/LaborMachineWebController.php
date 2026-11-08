<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Labor\LaborCostService;
use App\Domain\Machine\MachineCostService;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\LaborRate;
use App\Models\Machine;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

final class LaborMachineWebController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('require.permission:labor_machines.view', only: ['index']),
            new Middleware('require.permission:labor_machines.manage', only: [
                'storeLabor',
                'updateLabor',
                'destroyLabor',
                'storeMachine',
                'updateMachine',
                'destroyMachine',
            ]),
        ];
    }

    public function __construct(
        private readonly LaborCostService $laborService = new LaborCostService,
        private readonly MachineCostService $machineService = new MachineCostService
    ) {}

    /**
     * Show labor rates and machines management.
     */
    public function index(): View
    {
        $business = Context::requireBusiness();

        $laborRates = LaborRate::where('business_id', $business->id)->latest()->get();
        $machines = Machine::where('business_id', $business->id)->latest()->get();

        return view('app.labor-machines.index', compact('business', 'laborRates', 'machines'));
    }

    /**
     * Store new labor rate.
     */
    public function storeLabor(Request $request): RedirectResponse
    {
        $business = Context::business() ?? Context::requireBusiness();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'basis' => ['required', 'string', 'in:hourly,daily,monthly,per_unit,per_project,per_task'],
            'rate_amount' => ['required', 'numeric', 'gt:0'],
            'working_days_per_month' => ['nullable', 'numeric', 'gt:0'],
            'working_hours_per_day' => ['nullable', 'numeric', 'gt:0'],
            'utilization_percentage' => ['nullable', 'numeric', 'gt:0', 'lte:100'],
            'is_subcontractor' => ['nullable', 'boolean'],
        ]);

        $laborRate = LaborRate::create([
            'business_id' => $business->id,
            'name' => $validated['name'],
            'basis' => $validated['basis'],
            'rate_amount' => (float) $validated['rate_amount'],
            'working_days_per_month' => (int) ($validated['working_days_per_month'] ?? 22),
            'working_hours_per_day' => (float) ($validated['working_hours_per_day'] ?? 8),
            'utilization_rate' => (float) ($validated['utilization_percentage'] ?? 80),
            'is_subcontractor' => (bool) ($validated['is_subcontractor'] ?? false),
        ]);

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $request->user()?->id,
            'auditable_type' => LaborRate::class,
            'auditable_id' => $laborRate->id,
            'action' => 'labor_rates.created',
            'risk_level' => AuditLog::RISK_LOW,
            'notes' => "Menambahkan tarif tenaga kerja: {$laborRate->name} ({$laborRate->basis})",
            'new_values' => [
                'name' => $laborRate->name,
                'basis' => $laborRate->basis,
                'rate_amount' => (float) $laborRate->rate_amount,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('labor-machines.index')->with('success', 'Tarif tenaga kerja berhasil ditambahkan.');
    }

    /**
     * Update an existing labor rate.
     */
    public function updateLabor(Request $request, LaborRate $laborRate): RedirectResponse
    {
        $business = Context::business();
        abort_unless($business !== null, 403);
        abort_unless($laborRate->business_id === $business->id, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'basis' => ['required', 'string', 'in:hourly,daily,monthly,per_unit,per_project,per_task'],
            'rate_amount' => ['required', 'numeric', 'gt:0'],
            'working_days_per_month' => ['nullable', 'numeric', 'gt:0'],
            'working_hours_per_day' => ['nullable', 'numeric', 'gt:0'],
            'utilization_percentage' => ['nullable', 'numeric', 'gt:0', 'lte:100'],
            'is_subcontractor' => ['nullable', 'boolean'],
        ]);

        $oldValues = [
            'name' => $laborRate->name,
            'basis' => $laborRate->basis,
            'rate_amount' => (float) $laborRate->rate_amount,
            'working_days_per_month' => $laborRate->working_days_per_month,
            'working_hours_per_day' => (float) $laborRate->working_hours_per_day,
            'utilization_rate' => (float) $laborRate->utilization_rate,
            'is_subcontractor' => (bool) $laborRate->is_subcontractor,
        ];

        $laborRate->update([
            'name' => $validated['name'],
            'basis' => $validated['basis'],
            'rate_amount' => (float) $validated['rate_amount'],
            'working_days_per_month' => (int) ($validated['working_days_per_month'] ?? 22),
            'working_hours_per_day' => (float) ($validated['working_hours_per_day'] ?? 8),
            'utilization_rate' => (float) ($validated['utilization_percentage'] ?? 80),
            'is_subcontractor' => (bool) ($validated['is_subcontractor'] ?? false),
        ]);

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $request->user()?->id,
            'auditable_type' => LaborRate::class,
            'auditable_id' => $laborRate->id,
            'action' => 'labor_rates.updated',
            'risk_level' => AuditLog::RISK_LOW,
            'notes' => "Memperbarui tarif tenaga kerja: {$laborRate->name}",
            'old_values' => $oldValues,
            'new_values' => [
                'name' => $laborRate->name,
                'basis' => $laborRate->basis,
                'rate_amount' => (float) $laborRate->rate_amount,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('labor-machines.index')->with('success', 'Tarif tenaga kerja berhasil diperbarui.');
    }

    /**
     * Store new machine.
     */
    public function storeMachine(Request $request): RedirectResponse
    {
        $business = Context::business() ?? Context::requireBusiness();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'purchase_price' => ['required', 'numeric', 'gt:0'],
            'useful_life_hours' => ['required', 'numeric', 'gt:0'],
            'power_kw' => ['nullable', 'numeric', 'gte:0'],
            'electricity_cost_per_kwh' => ['nullable', 'numeric', 'gte:0'],
            'maintenance_cost_per_hour' => ['nullable', 'numeric', 'gte:0'],
            'salvage_value' => ['nullable', 'numeric', 'gte:0'],
        ]);

        $powerKw = (float) ($validated['power_kw'] ?? 0);
        $elecRate = (float) ($validated['electricity_cost_per_kwh'] ?? 1500);

        $machine = Machine::create([
            'business_id' => $business->id,
            'name' => $validated['name'],
            'purchase_price' => (float) $validated['purchase_price'],
            'residual_value' => (float) ($validated['salvage_value'] ?? 0),
            'useful_life_hours' => (float) $validated['useful_life_hours'],
            'maintenance_cost_per_hour' => (float) ($validated['maintenance_cost_per_hour'] ?? 0),
            'electricity_cost_per_hour' => $powerKw * $elecRate,
        ]);

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $request->user()?->id,
            'auditable_type' => Machine::class,
            'auditable_id' => $machine->id,
            'action' => 'machines.created',
            'risk_level' => AuditLog::RISK_LOW,
            'notes' => "Menambahkan mesin/peralatan: {$machine->name}",
            'new_values' => [
                'name' => $machine->name,
                'purchase_price' => (float) $machine->purchase_price,
                'useful_life_hours' => (float) $machine->useful_life_hours,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('labor-machines.index')->with('success', 'Mesin / Peralatan berhasil ditambahkan.');
    }

    /**
     * Update an existing machine.
     */
    public function updateMachine(Request $request, Machine $machine): RedirectResponse
    {
        $business = Context::business();
        abort_unless($business !== null, 403);
        abort_unless($machine->business_id === $business->id, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'purchase_price' => ['required', 'numeric', 'gt:0'],
            'useful_life_hours' => ['required', 'numeric', 'gt:0'],
            'power_kw' => ['nullable', 'numeric', 'gte:0'],
            'electricity_cost_per_kwh' => ['nullable', 'numeric', 'gte:0'],
            'maintenance_cost_per_hour' => ['nullable', 'numeric', 'gte:0'],
            'salvage_value' => ['nullable', 'numeric', 'gte:0'],
        ]);

        $powerKw = (float) ($validated['power_kw'] ?? 0);
        $elecRate = (float) ($validated['electricity_cost_per_kwh'] ?? 1500);

        $oldValues = [
            'name' => $machine->name,
            'purchase_price' => (float) $machine->purchase_price,
            'residual_value' => (float) $machine->residual_value,
            'useful_life_hours' => (float) $machine->useful_life_hours,
            'maintenance_cost_per_hour' => (float) $machine->maintenance_cost_per_hour,
            'electricity_cost_per_hour' => (float) $machine->electricity_cost_per_hour,
        ];

        $machine->update([
            'name' => $validated['name'],
            'purchase_price' => (float) $validated['purchase_price'],
            'residual_value' => (float) ($validated['salvage_value'] ?? 0),
            'useful_life_hours' => (float) $validated['useful_life_hours'],
            'maintenance_cost_per_hour' => (float) ($validated['maintenance_cost_per_hour'] ?? 0),
            'electricity_cost_per_hour' => $powerKw * $elecRate,
        ]);

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $request->user()?->id,
            'auditable_type' => Machine::class,
            'auditable_id' => $machine->id,
            'action' => 'machines.updated',
            'risk_level' => AuditLog::RISK_LOW,
            'notes' => "Memperbarui data mesin: {$machine->name}",
            'old_values' => $oldValues,
            'new_values' => [
                'name' => $machine->name,
                'purchase_price' => (float) $machine->purchase_price,
                'useful_life_hours' => (float) $machine->useful_life_hours,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('labor-machines.index')->with('success', 'Data mesin berhasil diperbarui.');
    }

    /**
     * Delete labor rate.
     */
    public function destroyLabor(LaborRate $laborRate): RedirectResponse
    {
        $business = Context::business();
        abort_unless($business !== null, 403);
        abort_unless($laborRate->business_id === $business->id, 403);

        $oldValues = [
            'name' => $laborRate->name,
            'basis' => $laborRate->basis,
            'rate_amount' => (float) $laborRate->rate_amount,
        ];
        $laborRateId = $laborRate->id;
        $laborRateName = $laborRate->name;

        $laborRate->delete();

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => request()->user()?->id,
            'auditable_type' => LaborRate::class,
            'auditable_id' => $laborRateId,
            'action' => 'labor_rates.deleted',
            'risk_level' => AuditLog::RISK_MEDIUM,
            'notes' => "Menghapus tarif tenaga kerja: {$laborRateName}",
            'old_values' => $oldValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('labor-machines.index')->with('success', 'Tarif tenaga kerja berhasil dihapus.');
    }

    /**
     * Delete machine.
     */
    public function destroyMachine(Machine $machine): RedirectResponse
    {
        $business = Context::business();
        abort_unless($business !== null, 403);
        abort_unless($machine->business_id === $business->id, 403);

        $oldValues = [
            'name' => $machine->name,
            'purchase_price' => (float) $machine->purchase_price,
        ];
        $machineId = $machine->id;
        $machineName = $machine->name;

        $machine->delete();

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => request()->user()?->id,
            'auditable_type' => Machine::class,
            'auditable_id' => $machineId,
            'action' => 'machines.deleted',
            'risk_level' => AuditLog::RISK_MEDIUM,
            'notes' => "Menghapus mesin/peralatan: {$machineName}",
            'old_values' => $oldValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('labor-machines.index')->with('success', 'Mesin / Peralatan berhasil dihapus.');
    }
}

