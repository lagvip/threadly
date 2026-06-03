<?php

namespace App\Http\Requests\Checkout;

use App\DTOs\Checkout\CheckoutAddressData;
use App\Http\Requests\Concerns\NormalizesVietnamPhone;
use Illuminate\Foundation\Http\FormRequest;

class StoreCheckoutAddressRequest extends FormRequest
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
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => $this->vietnamPhoneRules(),
            'province' => ['required', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'ward' => ['required', 'string', 'max:255'],
            'detailed_address' => ['required', 'string', 'max:255'],
            'ghn_province_id' => ['required', 'integer'],
            'ghn_district_id' => ['required', 'integer'],
            'ghn_ward_code' => ['required', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return $this->phoneMessages();
    }

    public function toDTO(): CheckoutAddressData
    {
        return new CheckoutAddressData(
            recipientName: (string) $this->input('recipient_name'),
            phone: (string) $this->input('phone'),
            province: (string) $this->input('province'),
            district: (string) $this->input('district'),
            ward: (string) $this->input('ward'),
            detailedAddress: (string) $this->input('detailed_address'),
            ghnProvinceId: (int) $this->input('ghn_province_id'),
            ghnDistrictId: (int) $this->input('ghn_district_id'),
            ghnWardCode: (string) $this->input('ghn_ward_code'),
        );
    }
}
