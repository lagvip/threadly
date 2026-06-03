<?php

namespace App\Http\Requests\Client\Orders;

use Illuminate\Foundation\Http\FormRequest;

class IndexClientOrdersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'order_code' => ['nullable', 'string', 'max:100'],
            'customer' => ['nullable', 'string', 'max:250'],
            'payment_status' => ['nullable', 'string', 'max:50'],
            'order_status' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function filters(): array
    {
        return [
            'order_code' => trim((string) $this->input('order_code')),
            'customer' => trim((string) $this->input('customer')),
            'payment_status' => $this->input('payment_status'),
            'order_status' => $this->input('order_status'),
        ];
    }
}
