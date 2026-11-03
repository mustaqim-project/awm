<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Business $resource
 */
final class BusinessResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'description' => $this->resource->description,
            'template_code' => $this->resource->template_code,
            'industry_category' => $this->resource->industry_category,
            'is_food_industry' => $this->resource->isFoodIndustry(),
            'has_dine_in' => $this->resource->hasDineInFeature(),
            'is_workshop' => $this->resource->isWorkshop(),
            'is_laundry' => $this->resource->isLaundry(),
            'is_pharmacy' => $this->resource->isPharmacy(),
            'is_retail' => $this->resource->isRetailSector(),
            'is_manufacturing' => $this->resource->isManufacturingSector(),
            'is_service' => $this->resource->isService(),
            'role' => $this->whenPivotLoaded('business_users', fn () => $this->resource->pivot->role),
            'created_at' => $this->resource->created_at?->toIso8601String(),
            'updated_at' => $this->resource->updated_at?->toIso8601String(),
        ];
    }
}
