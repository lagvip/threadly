<?php

namespace App\Http\Requests\Admin\Refunds;

use Illuminate\Foundation\Http\FormRequest;

class RestockRefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'restock_note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
