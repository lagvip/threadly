<?php

namespace App\Http\Requests\Checkout;

use App\DTOs\Checkout\BuyNowData;
use Illuminate\Foundation\Http\FormRequest;

class BuyNowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'variant_id' => ['required', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function toDTO(): BuyNowData
    {
        return new BuyNowData(
            variantId: (int) $this->input('variant_id'),
            quantity: (int) $this->input('quantity'),
        );
    }
}
