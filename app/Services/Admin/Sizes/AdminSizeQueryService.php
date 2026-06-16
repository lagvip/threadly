<?php

namespace App\Services\Admin\Sizes;

use App\Contracts\Repositories\SizeRepositoryInterface;
use App\Support\Pagination;

class AdminSizeQueryService
{
    public function __construct(protected SizeRepositoryInterface $sizes) {}

    public function indexData(string $keyword): array
    {
        return [
            'sizes' => Pagination::withQueryString($this->sizes->paginatedForAdmin($keyword)),
            'keyword' => $keyword,
        ];
    }

    public function trashData(string $keyword): array
    {
        return [
            'sizes' => Pagination::withQueryString($this->sizes->paginatedForAdmin($keyword, true)),
            'keyword' => $keyword,
        ];
    }
}
