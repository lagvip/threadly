<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class SelectCheckoutItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'selected_items' => ['required', 'array', 'min:1'],
            'selected_items.*' => ['integer'],
        ];
    }
}
