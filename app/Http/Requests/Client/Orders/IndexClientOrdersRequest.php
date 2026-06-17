<?php

namespace App\Http\Requests\Client\Orders;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'payment_status' => ['nullable', Rule::in(OrderPaymentStatus::values())],
            'order_status' => ['nullable', Rule::in(OrderStatus::values())],
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
