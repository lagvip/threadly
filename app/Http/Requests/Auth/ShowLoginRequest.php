<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ShowLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'redirect' => ['nullable', 'string'],
        ];
    }

    public function intendedRedirect(): ?string
    {
        return $this->filled('redirect') ? (string) $this->input('redirect') : null;
    }
}
