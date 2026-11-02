<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Crm;

use App\Domain\Crm\LoyaltyService;
use App\Domain\Pos\PosShiftService;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\PosCashMovement;
use App\Models\PosOrder;
use App\Models\PosOrderPayment;
use App\Models\PosShift;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class CustomerDebtApiController extends Controller
{
    public function __construct(
        private readonly LoyaltyService $loyaltyService = new LoyaltyService(),
        private readonly PosShiftService $shiftService = new PosShiftService()
    ) {}

    /**
     * Retrieve customer debt overview, available credit, active unpaid bills, and credit transactions ledger.
     * GET /api/v1/customers/{customer}/debts
     */
    public function debts(Request $request, Customer|string $customer): JsonResponse
    {
        $business = Context::requireBusiness();

        if (is_string($customer)) {
            $customer = Customer::where('business_id', $business->id)
                ->where(function ($q) use ($customer): void {
                    $q->where('id', $customer)->orWhere('slug', $customer);
                })
                ->first();

            if (! $customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pelanggan tidak ditemukan di bisnis ini.',
                ], Response::HTTP_NOT_FOUND);
            }
        } elseif ($customer->business_id !== $business->id) {
            return response()->json([
                'success' => false,
                'message' => 'Pelanggan tidak ditemukan di bisnis ini.',
            ], Response::HTTP_NOT_FOUND);
        }

        $perPage = max(1, min(100, (int) $request->query('per_page', 20)));
        $transactionsQuery = $customer->creditTransactions()->latest();
        $transactions = $transactionsQuery->paginate($perPage);

        $creditLimit = (float) $customer->credit_limit;
        $currentCredit = (float) $customer->current_credit_balance;
        $availableCredit = max(0.0, $creditLimit - $currentCredit);

        // Fetch recent POS orders paid with customer credit / tempo
        $unpaidOrders = PosOrder::where('business_id', $business->id)
            ->where('customer_id', $customer->id)
            ->whereHas('payments', function ($q): void {
                $q->where('payment_method', PosOrderPayment::METHOD_CUSTOMER_CREDIT);
            })
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (PosOrder $order): array => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'order_date' => $order->order_date ? $order->order_date->format('Y-m-d') : $order->created_at->format('Y-m-d'),
                'total_amount' => (float) $order->total_amount,
                'unpaid_amount' => (float) $order->total_amount,
                'notes' => $order->notes ?? 'Belanja tempo POS',
            ]);

        return response()->json([
            'success' => true,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'company_name' => $customer->company_name,
                'credit_limit' => $creditLimit,
                'current_credit_balance' => $currentCredit,
                'available_credit' => $availableCredit,
                'payment_terms_days' => (int) ($customer->payment_terms_days ?? 30),
            ],
            'unpaid_orders' => $unpaidOrders,
            'transactions' => $transactions->items(),
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Record repayment / installment against customer credit balance.
     * POST /api/v1/customers/{customer}/repay-debt
     */
    public function repayDebt(Request $request, Customer|string $customer): JsonResponse
    {
        $business = Context::requireBusiness();

        if (is_string($customer)) {
            $customer = Customer::where('business_id', $business->id)
                ->where(function ($q) use ($customer): void {
                    $q->where('id', $customer)->orWhere('slug', $customer);
                })
                ->first();

            if (! $customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pelanggan tidak ditemukan di bisnis ini.',
                ], Response::HTTP_NOT_FOUND);
            }
        } elseif ($customer->business_id !== $business->id) {
            return response()->json([
                'success' => false,
                'message' => 'Pelanggan tidak ditemukan di bisnis ini.',
            ], Response::HTTP_NOT_FOUND);
        }

        $currentBalance = (float) $customer->current_credit_balance;
        if ($currentBalance <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Pelanggan tidak memiliki sisa saldo piutang / kasbon aktif.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:' . $currentBalance],
            'payment_method' => ['nullable', 'string', 'in:cash,transfer,bank_transfer,qris'],
            'cash_account_id' => ['nullable', 'uuid', 'exists:cash_accounts,id'],
            'notes' => ['nullable', 'string', 'max:255'],
            'pos_shift_id' => ['nullable', 'uuid', 'exists:pos_shifts,id'],
            'send_wa' => ['nullable', 'boolean'],
        ], [
            'amount.max' => 'Jumlah pembayaran piutang tidak boleh melebihi sisa piutang aktif (Rp ' . number_format($currentBalance, 0, ',', '.') . ').',
            'amount.min' => 'Jumlah pembayaran piutang minimal Rp 1.',
        ]);

        $amount = (float) $validated['amount'];
        $notes = $validated['notes'] ?? 'Pelunasan piutang pelanggan';
        $paymentMethod = $validated['payment_method'] ?? 'cash';
        $user = $request->user();

        try {
            // Atomic anti-lapping repayment recording & ledger sync
            $transaction = $this->loyaltyService->recordCustomerCreditPayment(
                customer: $customer,
                amount: $amount,
                notes: $notes,
                user: $user
            );

            // If cash repayment recorded at active POS terminal shift, record cash-in movement
            if (! empty($validated['pos_shift_id']) && in_array($paymentMethod, ['cash'], true)) {
                $shift = PosShift::where('business_id', $business->id)->find($validated['pos_shift_id']);
                if ($shift && $shift->isOpen()) {
                    $this->shiftService->recordCashMovement(
                        shift: $shift,
                        type: PosCashMovement::TYPE_CASH_IN,
                        amount: $amount,
                        reason: "Cicilan piutang: {$customer->name}" . ($notes ? " ({$notes})" : ''),
                        user: $user
                    );
                }
            }

            $waReceiptUrl = $this->loyaltyService->generateCreditPaymentWhatsAppReceiptUrl($customer, $transaction);

            $customer->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran piutang berhasil dicatat dan dibukukan ke buku kas.',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'amount_paid' => $amount,
                    'previous_balance' => $currentBalance,
                    'remaining_balance' => (float) $customer->current_credit_balance,
                    'wa_receipt_url' => $waReceiptUrl,
                    'whatsapp_url' => $waReceiptUrl,
                    'sent_via_gateway' => false,
                    'customer' => [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'current_credit_balance' => (float) $customer->current_credit_balance,
                    ],
                ],
            ], Response::HTTP_OK);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
