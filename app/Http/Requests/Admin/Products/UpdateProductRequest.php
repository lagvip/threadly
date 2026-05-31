<?php

namespace App\Http\Requests\Admin\Products;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $productId = $this->route('id');

        return [
            'name' => ['required', 'string', 'max:250', 'unique:products,name,' . $productId],
            'description' => ['nullable', 'string'],
            'id_brand' => ['required', 'exists:brands,id'],
            'id_category' => ['required', 'exists:categories,id'],
            'image_primary' => ['nullable', 'image', 'mimes:jpg,png,jpeg', 'max:2048'],
            'status' => ['required', 'in:active,inactive'],

            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'exists:product_variants,id'],
            'variants.*.id_color' => ['required', 'exists:colors,id'],
            'variants.*.id_size' => ['required', 'exists:sizes,id'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.quantity' => ['nullable', 'integer', 'min:0'],
            'variants.*.status' => ['nullable', 'in:active,inactive'],
            'variants.*.image' => ['nullable', 'image', 'mimes:jpg,png,jpeg', 'max:2048'],
            'variants.*.delete' => ['nullable', 'in:0,1'],

            'variants_new' => ['nullable', 'array'],
            'variants_new.*.id_color' => ['required', 'exists:colors,id'],
            'variants_new.*.id_size' => ['required', 'exists:sizes,id'],
            'variants_new.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants_new.*.quantity' => ['nullable', 'integer', 'min:0'],
            'variants_new.*.status' => ['nullable', 'in:active,inactive'],
            'variants_new.*.image' => ['nullable', 'image', 'mimes:jpg,png,jpeg', 'max:2048'],
        ];
    }
}
