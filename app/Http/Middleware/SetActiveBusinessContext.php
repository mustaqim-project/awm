<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Business;
use App\Models\BusinessMembership;
use App\Support\Context;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SetActiveBusinessContext
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null) {
            $businessId = $request->header('X-Business-Id')
                ?: ($request->hasSession() ? $request->session()->get('active_business_id') : null)
                ?: $user->active_business_id;

            if ($businessId !== null) {
                /** @var BusinessMembership|null $membership */
                $membership = BusinessMembership::where('business_id', $businessId)
                    ->where('user_id', $user->id)
                    ->first();

                if ($membership !== null) {
                    $business = Business::find($businessId);

                    if ($business !== null) {
                        Context::setBusiness($business, $membership);
                    }
                }
            }

            // Fallback: Bind first active business membership if context is still unbound
            if (! Context::hasBusiness()) {
                $fallbackMembership = BusinessMembership::where('user_id', $user->id)->first();
                if ($fallbackMembership !== null) {
                    $fallbackBusiness = Business::find($fallbackMembership->business_id);
                    if ($fallbackBusiness !== null) {
                        Context::setBusiness($fallbackBusiness, $fallbackMembership);
                        if (! $user->active_business_id) {
                            $user->updateQuietly(['active_business_id' => $fallbackBusiness->id]);
                        }
                    }
                } else {
                    $bizName = 'Usaha ' . ($user->name ?: 'COOCA');
                    $newBiz = Business::create([
                        'name' => $bizName,
                        'slug' => \Illuminate\Support\Str::slug($bizName) . '-' . \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(5)),
                        'currency' => 'IDR',
                        'currency_symbol' => 'Rp',
                        'currency_precision' => 0,
                        'is_active' => true,
                        'template_code' => 'retail_reseller',
                        'industry_category' => 'retail',
                    ]);
                    $newMembership = BusinessMembership::create([
                        'id' => (string) \Illuminate\Support\Str::uuid(),
                        'business_id' => $newBiz->id,
                        'user_id' => $user->id,
                        'role' => 'owner',
                    ]);
                    $user->updateQuietly(['active_business_id' => $newBiz->id]);
                    Context::setBusiness($newBiz, $newMembership);
                }
            }
        }

        return $next($request);
    }
}
