<?php

namespace App\Http\Requests\Admin\Products;

use App\Enums\ProductStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:250', 'unique:products,name'],
            'description' => ['nullable', 'string'],
            'id_brand' => ['required', 'exists:brands,id'],
            'id_category' => ['required', 'exists:categories,id'],
            'image_primary' => ['required', 'image', 'mimes:jpg,png,jpeg', 'max:2048'],
            'status' => ['required', Rule::in(ProductStatus::values())],

            'variants' => ['required', 'array', 'min:1'],
            'variants.*.id_color' => ['required', 'exists:colors,id'],
            'variants.*.id_size' => ['required', 'exists:sizes,id'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.quantity' => ['nullable', 'integer', 'min:0'],
            'variants.*.status' => ['nullable', Rule::in(ProductStatus::values())],
            'variants.*.image' => ['nullable', 'image', 'mimes:jpg,png,jpeg', 'max:2048'],
        ];
    }
}
