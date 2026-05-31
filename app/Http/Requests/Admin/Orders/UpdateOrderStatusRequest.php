<?php

namespace App\Http\Requests\Admin\Orders;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'order_status' => ['required', Rule::in(OrderStatus::values())],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
