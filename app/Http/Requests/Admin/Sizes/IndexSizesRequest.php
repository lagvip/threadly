<?php

namespace App\Http\Requests\Admin\Sizes;

use Illuminate\Foundation\Http\FormRequest;

class IndexSizesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:255'],
            'from' => ['nullable', 'in:list,trash'],
        ];
    }

    public function keyword(): string
    {
        return trim((string) $this->get('keyword', ''));
    }
}
