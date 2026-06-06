<?php

namespace App\Http\Requests\Admin\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class IndexInventoryReceiptsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() === true;
    }

    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:draft,posted,cancelled'],
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'type' => ['nullable', 'in:import,sale,cancel_release,refund_restock,adjustment'],
        ];
    }

    public function filters(): array
    {
        return $this->validated();
    }
}
