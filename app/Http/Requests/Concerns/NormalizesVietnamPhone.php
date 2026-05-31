<?php

namespace App\Http\Requests\Concerns;

trait NormalizesVietnamPhone
{
    protected function normalizeVietnamPhone(?string $phone): string
    {
        return preg_replace('/\D+/', '', (string) $phone);
    }

    protected function vietnamPhoneRules(): array
    {
        return [
            'required',
            'digits:10',
            'regex:/^(03|05|07|08|09)[0-9]{8}$/',
        ];
    }

    protected function phoneMessages(): array
    {
        return [
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.digits' => 'Số điện thoại phải gồm đúng 10 số.',
            'phone.regex' => 'Số điện thoại phải là số di động Việt Nam hợp lệ.',
        ];
    }
}
