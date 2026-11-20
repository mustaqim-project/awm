<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Operations;

use App\Domain\Operations\Models\GoogleCalendarAccount;
use App\Domain\Operations\Services\GoogleCalendarSyncService;
use App\Http\Controllers\Controller;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GoogleCalendarWebController extends Controller
{
    public function __construct(
        protected GoogleCalendarSyncService $syncService
    ) {}

    public function redirect(): RedirectResponse
    {
        $business = Context::requireBusiness();
        $url = $this->syncService->getAuthUrl($business);

        return redirect()->away($url);
    }

    public function callback(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        $code = (string) $request->query('code');

        if (empty($code)) {
            $error = (string) $request->query('error', 'Google authorization was denied.');
            return redirect()->route('operations.calendar.settings')
                ->with('error', __('operations.google_sync_auth_denied') . ': ' . $error);
        }

        try {
            $account = $this->syncService->handleOAuthCallback($code, $business);

            // Trigger initial pull
            $syncedCount = $this->syncService->pullEvents($account);

            return redirect()->route('operations.calendar.index')
                ->with('success', __('operations.google_sync_connected_success', ['count' => $syncedCount]));
        } catch (\Throwable $e) {
            return redirect()->route('operations.calendar.settings')
                ->with('error', $e->getMessage());
        }
    }

    public function syncNow(Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        $user = Context::user();
        $accountId = $request->input('account_id');

        $account = null;
        if ($accountId) {
            $account = GoogleCalendarAccount::where('business_id', $business->id)
                ->where('id', $accountId)
                ->where('is_active', true)
                ->first();
        } elseif ($user) {
            $account = GoogleCalendarAccount::where('business_id', $business->id)
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->first();
        }

        if (! $account) {
            $account = GoogleCalendarAccount::where('business_id', $business->id)
                ->where('is_active', true)
                ->first();
        }

        if (! $account) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('operations.google_sync_not_connected'),
                ], 400);
            }
            return back()->with('error', __('operations.google_sync_not_connected'));
        }

        $synced = $this->syncService->pullEvents($account);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('operations.google_sync_completed_success', ['count' => $synced]),
                'synced_count' => $synced,
                'last_synced_at' => $account->fresh()->last_synced_at?->diffForHumans(),
            ]);
        }

        return back()->with('success', __('operations.google_sync_completed_success', ['count' => $synced]));
    }

    public function disconnect(Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        $user = Context::user();
        $accountId = $request->input('account_id');

        $account = null;
        if ($accountId) {
            $account = GoogleCalendarAccount::where('business_id', $business->id)
                ->where('id', $accountId)
                ->where('is_active', true)
                ->first();
        } elseif ($user) {
            $account = GoogleCalendarAccount::where('business_id', $business->id)
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->first();
        }

        if (! $account) {
            $account = GoogleCalendarAccount::where('business_id', $business->id)
                ->where('is_active', true)
                ->first();
        }

        if ($account) {
            $account->is_active = false;
            $account->save();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('operations.google_sync_disconnected_success'),
            ]);
        }

        return redirect()->route('operations.calendar.settings')
            ->with('success', __('operations.google_sync_disconnected_success'));
    }
}
