<?php

namespace App\Http\Requests\Admin\OrderDetails;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'id_order' => ['required_without:order_id', 'exists:orders,id'],
            'order_id' => ['required_without:id_order', 'exists:orders,id'],
            'id_variant' => ['required_without:variant_id', 'exists:product_variants,id'],
            'variant_id' => ['required_without:id_variant', 'exists:product_variants,id'],
            'variant_data' => ['nullable', 'array'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
