<?php

namespace App\Http\Requests\Admin\Products;

use Illuminate\Foundation\Http\FormRequest;

class IndexProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:250'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ];
    }

    public function filters(): array
    {
        return [
            'search' => trim((string) $this->input('search')),
            'brand_id' => $this->input('brand_id'),
            'category_id' => $this->input('category_id'),
        ];
    }
}
