<?php

namespace App\Http\Requests\Admin\Banners;

use Illuminate\Foundation\Http\FormRequest;

class BulkBannerIdsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ];
    }

    public function ids(): array
    {
        return array_values(array_filter($this->input('ids', []), fn ($id) => is_numeric($id)));
    }
}
