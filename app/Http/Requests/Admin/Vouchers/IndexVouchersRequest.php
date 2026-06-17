<?php

namespace App\Http\Requests\Admin\Vouchers;

use App\Enums\VoucherStatus;
use App\Enums\VoucherType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexVouchersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', Rule::in(VoucherType::values())],
            'status' => ['nullable', Rule::in(VoucherStatus::values())],
        ];
    }

    public function filters(): array
    {
        return [
            'search' => trim((string) $this->get('search')),
            'type' => $this->get('type'),
            'status' => $this->get('status'),
        ];
    }
}
