<?php

namespace App\Http\Requests\Admin\Vouchers;

use Illuminate\Foundation\Http\FormRequest;

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
            'type' => ['nullable', 'in:percent,fixed'],
            'status' => ['nullable', 'string', 'max:50'],
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
