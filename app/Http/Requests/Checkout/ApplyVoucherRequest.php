<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class ApplyVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'voucher_code' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'voucher_code.required' => 'Vui lòng nhập mã giảm giá.',
        ];
    }
}
