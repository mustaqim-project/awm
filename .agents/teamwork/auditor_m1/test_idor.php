<?php

require __DIR__ . '/../../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$biz1 = App\Models\Business::first();
$biz2 = App\Models\Business::where('id', '!=', $biz1->id)->first() ?? App\Models\Business::create(['name' => 'Adversarial Tenant Inc']);
$controller = app(App\Http\Controllers\Web\LaborMachineWebController::class);

$results = [];

// 1. HasMiddleware Verification
$results['has_middleware_interface'] = is_subclass_of(App\Http\Controllers\Web\LaborMachineWebController::class, Illuminate\Routing\Controllers\HasMiddleware::class);
$middleware = App\Http\Controllers\Web\LaborMachineWebController::middleware();
$results['middleware_definition_count'] = count($middleware);

// 2. IDOR on Cross-Tenant Labor Rate Update
$lrForeign = App\Models\LaborRate::create([
    'business_id' => $biz2->id,
    'name' => 'Foreign Worker ' . uniqid(),
    'basis' => 'hourly',
    'rate_amount' => 50000,
]);
App\Support\Context::setBusiness($biz1);

try {
    $req = Illuminate\Http\Request::create('/labor-rates/' . $lrForeign->id, 'PUT', [
        'name' => 'Hacked Name',
        'basis' => 'hourly',
        'rate_amount' => 60000,
    ]);
    $controller->updateLabor($req, $lrForeign);
    $results['idor_update_labor'] = 'FAILED_NOT_BLOCKED';
} catch (Symfony\Component\HttpKernel\Exception\HttpException $e) {
    $results['idor_update_labor'] = 'BLOCKED_HTTP_' . $e->getStatusCode();
}

// 3. IDOR on Cross-Tenant Labor Rate Delete
try {
    $controller->destroyLabor($lrForeign);
    $results['idor_destroy_labor'] = 'FAILED_NOT_BLOCKED';
} catch (Symfony\Component\HttpKernel\Exception\HttpException $e) {
    $results['idor_destroy_labor'] = 'BLOCKED_HTTP_' . $e->getStatusCode();
}
$lrForeign->forceDelete();

// 4. IDOR on Cross-Tenant Machine Update & Delete
$mcForeign = App\Models\Machine::create([
    'business_id' => $biz2->id,
    'name' => 'Foreign Machine ' . uniqid(),
    'purchase_price' => 10000000,
    'useful_life_hours' => 5000,
]);

try {
    $req = Illuminate\Http\Request::create('/machines/' . $mcForeign->id, 'PUT', [
        'name' => 'Hacked Machine',
        'purchase_price' => 20000000,
        'useful_life_hours' => 6000,
    ]);
    $controller->updateMachine($req, $mcForeign);
    $results['idor_update_machine'] = 'FAILED_NOT_BLOCKED';
} catch (Symfony\Component\HttpKernel\Exception\HttpException $e) {
    $results['idor_update_machine'] = 'BLOCKED_HTTP_' . $e->getStatusCode();
}

try {
    $controller->destroyMachine($mcForeign);
    $results['idor_destroy_machine'] = 'FAILED_NOT_BLOCKED';
} catch (Symfony\Component\HttpKernel\Exception\HttpException $e) {
    $results['idor_destroy_machine'] = 'BLOCKED_HTTP_' . $e->getStatusCode();
}
$mcForeign->forceDelete();

// 5. Audit Log Authenticity on Mutations
App\Support\Context::setBusiness($biz1);

// Store Labor
$lr = App\Models\LaborRate::create([
    'business_id' => $biz1->id,
    'name' => 'Audit Test Labor ' . uniqid(),
    'basis' => 'hourly',
    'rate_amount' => 45000,
]);
$req = Illuminate\Http\Request::create('/labor-rates/' . $lr->id, 'PUT', [
    'name' => 'Audit Test Labor Updated',
    'basis' => 'hourly',
    'rate_amount' => 52000,
]);
$controller->updateLabor($req, $lr);
$updateLaborAudit = App\Models\AuditLog::where('auditable_id', $lr->id)->where('action', 'labor_rates.updated')->first();
$results['audit_update_labor'] = ($updateLaborAudit !== null && isset($updateLaborAudit->old_values['rate_amount']));

$controller->destroyLabor($lr);
$destroyLaborAudit = App\Models\AuditLog::where('auditable_id', $lr->id)->where('action', 'labor_rates.deleted')->first();
$results['audit_destroy_labor'] = ($destroyLaborAudit !== null && $destroyLaborAudit->risk_level === App\Models\AuditLog::RISK_MEDIUM);

// Store Machine
$mc = App\Models\Machine::create([
    'business_id' => $biz1->id,
    'name' => 'Audit Test Machine ' . uniqid(),
    'purchase_price' => 12000000,
    'useful_life_hours' => 4000,
]);
$req = Illuminate\Http\Request::create('/machines/' . $mc->id, 'PUT', [
    'name' => 'Audit Test Machine Updated',
    'purchase_price' => 13000000,
    'useful_life_hours' => 4500,
]);
$controller->updateMachine($req, $mc);
$updateMachineAudit = App\Models\AuditLog::where('auditable_id', $mc->id)->where('action', 'machines.updated')->first();
$results['audit_update_machine'] = ($updateMachineAudit !== null && isset($updateMachineAudit->old_values['purchase_price']));

$controller->destroyMachine($mc);
$destroyMachineAudit = App\Models\AuditLog::where('auditable_id', $mc->id)->where('action', 'machines.deleted')->first();
$results['audit_destroy_machine'] = ($destroyMachineAudit !== null && $destroyMachineAudit->risk_level === App\Models\AuditLog::RISK_MEDIUM);

// 6. View Scoping
$allLaborCount = App\Models\LaborRate::count();
$biz1LaborCount = App\Models\LaborRate::where('business_id', $biz1->id)->count();
$viewData = $controller->index()->getData();
$results['index_labor_scoped_correctly'] = ($viewData['laborRates']->count() === $biz1LaborCount);
$results['index_machines_scoped_correctly'] = ($viewData['machines']->count() === App\Models\Machine::where('business_id', $biz1->id)->count());

echo json_encode($results, JSON_PRETTY_PRINT);
