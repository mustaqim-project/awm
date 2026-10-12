<?php

declare(strict_types=1);

namespace App\Http\Requests\Ai;

use App\Support\Context;
use Illuminate\Foundation\Http\FormRequest;

final class ExecuteAiPosActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = Context::user() ?? $this->user();
        if (! $user) {
            return false;
        }

        $actionType = $this->input('action_type');
        if ($actionType === 'create_invoice') {
            return Context::hasPermission('invoices.create') || Context::hasPermission('pos.orders') || Context::hasPermission('ai.approve');
        }

        if ($actionType === 'create_quotation') {
            return Context::hasPermission('quotations.create') || Context::hasPermission('pos.orders') || Context::hasPermission('ai.approve');
        }

        return Context::hasPermission('ai.access');
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'action_type' => ['required', 'string', 'in:create_invoice,create_quotation,create_purchase_order'],
            'payload' => ['required', 'array'],
            'payload.quantity' => ['nullable', 'numeric', 'min:0.001'],
            'payload.unit_price' => ['nullable', 'numeric', 'min:0'],
            'payload.unit_cost' => ['nullable', 'numeric', 'min:0'],
            'payload.total_amount' => ['nullable', 'numeric', 'min:0'],
            'payload.customer_id' => ['nullable', 'string'],
            'payload.customer_name' => ['nullable', 'string', 'max:150'],
            'payload.product_id' => ['nullable', 'string'],
            'payload.product_name' => ['nullable', 'string', 'max:150'],
            'payload.supplier_id' => ['nullable', 'string'],
            'payload.notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'action_type.required' => 'Jenis aksi AI wajib diisi.',
            'action_type.in' => 'Jenis aksi AI tidak didukung.',
            'payload.required' => 'Data payload aksi wajib disediakan.',
            'payload.quantity.min' => 'Jumlah kuantitas harus bernilai positif (minimal 0.001).',
            'payload.unit_price.min' => 'Harga satuan tidak boleh bernilai negatif.',
        ];
    }
}
