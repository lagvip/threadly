<?php

namespace App\Http\Requests\Admin\Refunds;

use Illuminate\Foundation\Http\FormRequest;

class RejectRefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'admin_note' => ['required', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'admin_note.required' => 'Vui lòng nhập lý do từ chối hoàn tiền.',
        ];
    }
}
