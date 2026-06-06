<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Pagination\LengthAwarePaginator;

class Pagination
{
    public static function withQueryString(LengthAwarePaginatorContract $paginator): LengthAwarePaginatorContract
    {
        if ($paginator instanceof LengthAwarePaginator) {
            return $paginator->withQueryString();
        }

        return $paginator;
    }
}
