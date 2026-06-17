<?php

namespace App\Http\Requests\Admin\Refunds;

use App\Enums\RefundRequestStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexRefundRequestsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in(RefundRequestStatus::values())],
            'keyword' => ['nullable', 'string', 'max:255'],
        ];
    }
}
