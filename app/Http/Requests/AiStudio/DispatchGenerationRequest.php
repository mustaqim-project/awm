<?php

declare(strict_types=1);

namespace App\Http\Requests\AiStudio;

use App\Support\Context;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DispatchGenerationRequest extends FormRequest
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
            'project_id' => ['required', 'uuid', Rule::exists('business_inspiration_projects', 'id')->where('business_id', $businessId)],
            'prompt_version_id' => ['nullable', 'uuid', 'exists:business_inspiration_prompt_versions,id'],
            'provider' => ['nullable', 'string', 'max:50'],
            'model_name' => ['nullable', 'string', 'max:100'],
            'aspect_ratio' => ['nullable', 'string', 'in:1:1,4:5,9:16,16:9'],
            'batch_count' => ['nullable', 'integer', 'min:1', 'max:4'],
            'enable_upscale' => ['nullable', 'boolean'],
            'idempotency_key' => ['nullable', 'string', 'max:100'],
            'custom_prompt' => ['nullable', 'string', 'max:2500'],
            'negative_prompt' => ['nullable', 'string', 'max:1000'],
            'sync' => ['nullable', 'boolean'],
        ];
    }
}
