<?php

namespace App\Http\Requests\Admin\Users;

use Illuminate\Foundation\Http\FormRequest;

class BanUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'ban_reason_option' => ['required', 'string'],
            'ban_reason_custom' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
