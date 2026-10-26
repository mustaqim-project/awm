<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Context;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureActiveBusiness
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Context::hasBusiness()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'No active business context selected. Please select an active business first.',
                    'error_code' => 'ACTIVE_BUSINESS_REQUIRED',
                ], Response::HTTP_CONFLICT);
            }

            return redirect()->route('businesses.select');
        }

        $business = Context::business();

        if ($business !== null && ! $business->is_active) {
            $isSafeMethod = in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true);

            // Block transaction execution screens like POS terminal even on GET
            $isTransactionCreationRoute = $request->routeIs(
                'pos.*',
                'invoices.create',
                'sales_orders.create',
                'expenses.create',
                'purchases.create',
                'purchase_orders.create',
                'bills.create',
                'payouts.create',
                'journal-entries.create'
            ) || $request->is('pos', 'pos/*');

            // If it is any mutating action (POST, PUT, PATCH, DELETE) or a transaction screen
            if (! $isSafeMethod || $isTransactionCreationRoute) {
                $reason = $business->suspended_reason ?: 'Akun bisnis ditangguhkan oleh administrator platform.';

                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success' => false,
                        'message' => "Akun bisnis ini sedang ditangguhkan (suspended). Seluruh transaksi dinonaktifkan: {$reason}",
                        'error_code' => 'BUSINESS_SUSPENDED',
                        'suspended_reason' => $reason,
                        'suspended_at' => $business->suspended_at?->toIso8601String(),
                    ], Response::HTTP_FORBIDDEN);
                }

                if ($isTransactionCreationRoute && $isSafeMethod) {
                    return redirect()->route('dashboard')
                        ->with('error', "Fitur transaksi dinonaktifkan sementara karena akun bisnis sedang ditangguhkan. Alasan: {$reason}");
                }

                return redirect()->back()
                    ->with('error', "Transaksi tidak dapat diproses. Akun bisnis Anda sedang ditangguhkan. Alasan: {$reason}");
            }
        }

        return $next($request);
    }
}
