<?php

namespace App\Http\Requests\Admin\Refunds;

use Illuminate\Foundation\Http\FormRequest;

class IndexRefundRequestsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', 'in:pending,approved,rejected,cancelled'],
            'keyword' => ['nullable', 'string', 'max:255'],
        ];
    }
}
