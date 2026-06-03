<?php

namespace App\Http\Requests\Admin\Orders;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexOrdersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'order_code' => ['nullable', 'string', 'max:255'],
            'customer' => ['nullable', 'string', 'max:255'],
            'payment_status' => ['nullable', 'string'],
            'order_status' => ['nullable', Rule::in(OrderStatus::values())],
        ];
    }
}
