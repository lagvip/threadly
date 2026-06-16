<?php

namespace App\Http\Requests\Checkout;

use App\DTOs\Checkout\VnpayCallbackData;
use Illuminate\Foundation\Http\FormRequest;

class VnpayCallbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            '*' => ['nullable'],
        ];
    }

    public function toDTO(): VnpayCallbackData
    {
        return VnpayCallbackData::fromArray($this->all());
    }
}
