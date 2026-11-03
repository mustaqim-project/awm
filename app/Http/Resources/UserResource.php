<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property User $resource
 */
final class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $activeBusiness = null;
        if ($this->resource->active_business_id) {
            $activeBusiness = \App\Models\Business::find($this->resource->active_business_id);
        }
        if ($activeBusiness === null) {
            $activeBusiness = $this->resource->businesses()->first();
            if ($activeBusiness !== null && $this->resource->active_business_id === null) {
                $this->resource->updateQuietly(['active_business_id' => $activeBusiness->id]);
            }
        }

        $role = \App\Support\Context::role();
        if ($role === null && $activeBusiness !== null) {
            $role = \App\Models\BusinessMembership::where('business_id', $activeBusiness->id)
                ->where('user_id', $this->resource->id)
                ->value('role');
        }
        $role = $role ?? 'owner';

        $permissions = \App\Support\Context::permissions();
        if (empty($permissions) && $role === 'owner') {
            $permissions = \App\Models\Permission::pluck('slug')->all();
        }

        $landingRoute = 'attendance';
        if ($role === 'owner' || in_array('dashboard.view', $permissions, true)) {
            $landingRoute = 'dashboard';
        } elseif (in_array('pos.terminal', $permissions, true)) {
            $landingRoute = 'pos';
        } elseif (in_array('pos.kitchen', $permissions, true) || in_array('pos.orders', $permissions, true)) {
            $landingRoute = 'kds';
        } elseif (in_array('inventory.view', $permissions, true) || in_array('warehouse.view', $permissions, true)) {
            $landingRoute = 'inventory';
        }

        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'email_verified_at' => $this->resource->email_verified_at?->toIso8601String(),
            'email_verified' => $this->resource->hasVerifiedEmail(),
            'require_email_verification' => false,
            'phone' => $this->resource->phone ?? null,
            'phone_verified_at' => $this->resource->phone_verified_at?->toIso8601String(),
            'avatar_url' => $this->resource->avatar ?? null,
            'active_business_id' => $activeBusiness?->id ?? $this->resource->active_business_id,
            'business' => $activeBusiness ? [
                'id' => $activeBusiness->id,
                'name' => $activeBusiness->name,
                'slug' => $activeBusiness->slug,
                'template_code' => $activeBusiness->template_code,
                'industry_category' => $activeBusiness->industry_category,
                'disabled_modules' => $activeBusiness->disabled_modules ?? \App\Domain\Template\ModuleRegistry::getDisabledModulesForTemplate($activeBusiness->template_code ?? ''),
                'is_food_industry' => $activeBusiness->isFoodIndustry(),
                'has_dine_in' => $activeBusiness->hasDineInFeature(),
                'is_workshop' => $activeBusiness->isWorkshop(),
                'is_laundry' => $activeBusiness->isLaundry(),
                'is_pharmacy' => $activeBusiness->isPharmacy(),
                'is_retail' => $activeBusiness->isRetailSector(),
                'is_manufacturing' => $activeBusiness->isManufacturingSector(),
                'is_service' => $activeBusiness->isService(),
                'is_subscribed' => $activeBusiness->hasActivePaidSubscription(),
                'subscription_plan' => $activeBusiness->subscription?->plan_code ?? 'free',
            ] : null,
            'businesses' => BusinessResource::collection($this->resource->businesses),
            'role' => $role,
            'is_owner' => $role === 'owner',
            'permissions' => $permissions,
            'can_view_margin' => $role === 'owner' || in_array('costing.view_margin', $permissions, true),
            'landing_route' => $landingRoute,
            'created_at' => $this->resource->created_at?->toIso8601String(),
            'updated_at' => $this->resource->updated_at?->toIso8601String(),
        ];
    }
}
