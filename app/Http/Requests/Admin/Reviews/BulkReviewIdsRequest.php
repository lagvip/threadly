<?php

namespace App\Http\Requests\Admin\Reviews;

use Illuminate\Foundation\Http\FormRequest;

class BulkReviewIdsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'ids' => ['nullable', 'array'],
            'ids.*' => ['integer'],
        ];
    }

    public function ids(): array
    {
        return collect($this->input('ids', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }
}
