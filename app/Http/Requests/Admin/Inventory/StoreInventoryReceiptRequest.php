<?php

namespace App\Http\Requests\Admin\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() === true;
    }

    public function rules(): array
    {
        return [
            'note' => ['nullable', 'string', 'max:2000'],
            'submit_action' => ['nullable', 'in:draft,post'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
            'items.*.note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Vui lòng thêm ít nhất một dòng sản phẩm.',
            'items.min' => 'Vui lòng thêm ít nhất một dòng sản phẩm.',
            'items.*.product_variant_id.required' => 'Vui lòng chọn biến thể sản phẩm.',
            'items.*.product_variant_id.exists' => 'Biến thể sản phẩm không tồn tại.',
            'items.*.quantity.required' => 'Vui lòng nhập số lượng.',
            'items.*.quantity.min' => 'Số lượng nhập phải lớn hơn 0.',
        ];
    }

    public function shouldPostNow(): bool
    {
        return $this->input('submit_action') === 'post';
    }
}
