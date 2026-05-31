<?php

namespace App\Http\Requests\Admin\Products;

use Illuminate\Foundation\Http\FormRequest;

class ToggleProductStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'boolean'],
        ];
    }

    public function statusValue(): string
    {
        return $this->boolean('status') ? 'active' : 'inactive';
    }
}
