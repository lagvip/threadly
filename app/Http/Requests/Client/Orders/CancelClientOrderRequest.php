<?php

namespace App\Http\Requests\Client\Orders;

use Illuminate\Foundation\Http\FormRequest;

class CancelClientOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'cancel_reason' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'cancel_reason.required' => 'Vui lòng chọn lý do hủy đơn.',
        ];
    }

    public function reason(): string
    {
        return trim((string) $this->input('cancel_reason'));
    }
}
