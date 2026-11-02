<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Pos;

use App\Domain\Finance\CashLedgerService;
use App\Domain\Pos\PosShiftService;
use App\Domain\Storage\TenantStorage;
use App\Http\Controllers\Controller;
use App\Models\CashTransaction;
use App\Models\Expense;
use App\Models\PosCashMovement;
use App\Models\PosShift;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class PosExpenseApiController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly PosShiftService $shiftService = new PosShiftService,
        private readonly CashLedgerService $cashLedgerService = new CashLedgerService
    ) {}

    /**
     * Get controller-level middleware.
     *
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('require.permission:pos.terminal,pos.orders'),
        ];
    }

    /**
     * Record a petty cash expense on an active cashier POS shift.
     */
    public function store(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        // 1. Validate request
        $validated = $request->validate([
            'pos_shift_id' => ['required'],
            'amount' => ['required', 'numeric', 'min:1'],
            'category' => ['required', 'string', 'max:100'],
            'notes' => ['required', 'string', 'max:500'],
            'receipt_image' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'receipt_image_base64' => ['nullable', 'string'],
        ]);

        // 2. Active Shift Check
        $shift = PosShift::where('business_id', $business->id)
            ->where('id', $validated['pos_shift_id'])
            ->first();

        if (! $shift || ! $shift->isOpen()) {
            return response()->json([
                'success' => false,
                'message' => 'Shift kasir telah ditutup atau tidak aktif.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // 3. Receipt Storage
        $receiptPath = null;
        $receiptUrl = null;

        if ($request->hasFile('receipt_image')) {
            $file = $request->file('receipt_image');
            $dir = TenantStorage::publicDir($business, TenantStorage::FOLDER_EXPENSES);
            $receiptPath = $file->store($dir, TenantStorage::DISK_PUBLIC);
            $receiptUrl = TenantStorage::url($receiptPath);
        } elseif (! empty($validated['receipt_image_base64'])) {
            $base64 = (string) $validated['receipt_image_base64'];
            if (str_contains($base64, ';base64,')) {
                $base64 = explode(';base64,', $base64)[1];
            }
            $binary = base64_decode($base64, true);
            if ($binary !== false) {
                $dir = TenantStorage::publicDir($business, TenantStorage::FOLDER_EXPENSES);
                $filename = Str::uuid()->toString() . '.jpg';
                $receiptPath = "{$dir}/{$filename}";
                Storage::disk(TenantStorage::DISK_PUBLIC)->put($receiptPath, $binary);
                $receiptUrl = TenantStorage::url($receiptPath);
            }
        }

        $user = $request->user();
        $amount = (float) $validated['amount'];
        $category = (string) $validated['category'];
        $notes = (string) $validated['notes'];

        // 4. Cash Movement
        $movement = $this->shiftService->recordCashMovement(
            shift: $shift,
            user: $user,
            type: PosCashMovement::TYPE_CASH_OUT,
            amount: $amount,
            reason: "[Kas Kecil - {$category}] {$notes}",
            notes: $notes
        );

        // 5. Cash Ledger Mutation
        $referenceId = (string) $shift->id;
        if (CashTransaction::where('business_id', $business->id)->where('reference_type', 'pos_shift_expense')->where('reference_id', $referenceId)->exists()) {
            $referenceId = (string) $movement->id;
        }

        $transaction = $this->cashLedgerService->recordOutflow(
            business: $business,
            amount: $amount,
            referenceType: 'pos_shift_expense',
            referenceId: $referenceId,
            description: "[Kasir - {$category}] {$notes}",
            method: 'cash',
            userId: (string) $user->id
        );

        // 6. Record Operational Expense entity
        $expenseNumber = 'EXP-POS-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        $expense = Expense::create([
            'business_id' => $business->id,
            'location_id' => $shift->location_id,
            'expense_number' => $expenseNumber,
            'expense_date' => now()->toDateString(),
            'category' => $category,
            'amount' => $amount,
            'payment_method' => 'cash',
            'description' => $notes,
            'receipt_image_path' => $receiptPath,
            'recorded_by' => $user->id,
        ]);

        // 7. Response per interface contract
        return response()->json([
            'success' => true,
            'message' => 'Pengeluaran kas kecil berhasil dicatat.',
            'data' => [
                'id' => $expense->id,
                'pos_shift_id' => $shift->id,
                'amount' => $amount,
                'category' => $category,
                'notes' => $notes,
                'receipt_url' => $receiptUrl,
                'cash_movement_id' => $movement->id,
                'cash_transaction_id' => $transaction->id,
                'created_at' => $expense->created_at?->toISOString() ?? now()->toISOString(),
            ],
        ], Response::HTTP_CREATED);
    }
}
