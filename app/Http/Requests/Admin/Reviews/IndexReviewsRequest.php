<?php

namespace App\Http\Requests\Admin\Reviews;

use Illuminate\Foundation\Http\FormRequest;

class IndexReviewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:replied,unreplied'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
        ];
    }

    public function filters(): array
    {
        return [
            'search' => trim((string) $this->input('search', '')),
            'status' => $this->input('status'),
            'rating' => $this->input('rating'),
        ];
    }
}
