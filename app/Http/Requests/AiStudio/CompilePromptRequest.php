<?php

declare(strict_types=1);

namespace App\Http\Requests\AiStudio;

use App\Support\Context;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompilePromptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Context::hasBusiness();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $businessId = Context::businessId();

        return [
            'project_id' => ['nullable', 'uuid', Rule::exists('business_inspiration_projects', 'id')->where('business_id', $businessId)],
            'product_id' => ['nullable', 'uuid', Rule::exists('products', 'id')->where('business_id', $businessId)],
            'custom_product_name' => ['nullable', 'string', 'max:150'],
            'name' => ['nullable', 'string', 'max:150'],
            'inspiration_item_id' => ['nullable', 'uuid', 'exists:inspiration_items,id'],
            'target_channel' => ['required', 'string', 'in:instagram,tiktok,shopee,tokopedia,facebook,banner'],
            'aspect_ratio' => ['required', 'string', 'in:1:1,4:5,9:16,16:9'],
            'style_preset' => ['required', 'string', 'max:50'],
            'custom_notes' => ['nullable', 'string', 'max:1000'],
            'provider' => ['nullable', 'string', 'max:50'],
            'model_name' => ['nullable', 'string', 'max:100'],
            'batch_count' => ['nullable', 'integer', 'min:1', 'max:4'],
            'enable_upscale' => ['nullable', 'boolean'],
        ];
    }
}
