<?php

namespace App\Services\Admin\Colors;

use App\Contracts\Repositories\ColorRepositoryInterface;
use App\Support\Pagination;

class AdminColorQueryService
{
    public function __construct(protected ColorRepositoryInterface $colors) {}

    public function indexData(string $keyword): array
    {
        return [
            'colors' => Pagination::withQueryString($this->colors->paginatedForAdmin($keyword)),
            'keyword' => $keyword,
        ];
    }

    public function binData(string $keyword): array
    {
        return [
            'colors' => Pagination::withQueryString($this->colors->paginatedForAdmin($keyword, true)),
            'keyword' => $keyword,
        ];
    }
}
