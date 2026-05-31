<?php

namespace App\Http\Requests\Client\Addresses;

use Illuminate\Foundation\Http\FormRequest;

class SaveAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20'],
            'province' => ['required', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'ward' => ['required', 'string', 'max:255'],
            'detailed_address' => ['required', 'string'],
            'ghn_province_id' => ['nullable', 'integer'],
            'ghn_district_id' => ['nullable', 'integer'],
            'ghn_ward_code' => ['nullable', 'string', 'max:50'],
            'address_type' => ['required', 'in:Home,Office,Other'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'recipient_name.required' => 'Vui lòng nhập tên người nhận.',
            'phone_number.required' => 'Vui lòng nhập số điện thoại.',
            'province.required' => 'Vui lòng nhập tỉnh / thành phố.',
            'ward.required' => 'Vui lòng nhập phường / xã.',
            'detailed_address.required' => 'Vui lòng nhập địa chỉ chi tiết.',
        ];
    }
}
