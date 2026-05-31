<?php

namespace App\Http\Requests\Client\Products;

use Illuminate\Foundation\Http\FormRequest;

class ProductSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'in:newest,price_asc,price_desc'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'brand' => ['nullable', 'array'],
            'brand.*' => ['integer', 'exists:brands,id'],
            'price_min' => ['nullable', 'numeric', 'min:0'],
            'price_max' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function filters(): array
    {
        return [
            'q' => trim((string) $this->input('q', '')),
            'sort' => (string) $this->input('sort', 'newest'),
            'category_id' => $this->input('category_id'),
            'brand' => array_values(array_filter((array) $this->input('brand', []))),
            'price_min' => $this->input('price_min'),
            'price_max' => $this->input('price_max'),
        ];
    }
}
