<?php

namespace App\Http\Requests\Checkout;

use App\DTOs\Checkout\CheckoutOrderData;
use App\Enums\PaymentMethod;
use App\Http\Requests\Concerns\NormalizesVietnamPhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCheckoutRequest extends FormRequest
{
    use NormalizesVietnamPhone;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => $this->normalizeVietnamPhone($this->input('phone')),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => $this->vietnamPhoneRules(),
            'address_id' => ['required', 'exists:addresses,id'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', Rule::in(PaymentMethod::values())],
        ];
    }

    public function messages(): array
    {
        return array_merge($this->phoneMessages(), [
            'address_id.required' => 'Vui lòng chọn địa chỉ nhận hàng.',
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán.',
        ]);
    }

    public function toDTO(): CheckoutOrderData
    {
        return new CheckoutOrderData(
            name: (string) $this->input('name'),
            phone: (string) $this->input('phone'),
            addressId: (int) $this->input('address_id'),
            customerNote: $this->input('customer_note'),
            paymentMethod: (string) $this->input('payment_method'),
        );
    }
}
