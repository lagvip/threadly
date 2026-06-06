<?php

namespace App\Services\Admin\Sizes;

use App\Contracts\Repositories\SizeRepositoryInterface;
use App\Support\Pagination;

class AdminSizeQueryService
{
    public function __construct(protected SizeRepositoryInterface $sizes)
    {
    }

    public function indexData(string $keyword): array
    {
        return [
            'sizes' => Pagination::withQueryString($this->filteredQuery($keyword)->latest()->paginate(10)),
            'keyword' => $keyword,
        ];
    }

    public function trashData(string $keyword): array
    {
        return [
            'sizes' => Pagination::withQueryString($this->filteredQuery($keyword, true)->latest('deleted_at')->paginate(10)),
            'keyword' => $keyword,
        ];
    }

    protected function filteredQuery(string $keyword, bool $trashed = false)
    {
        $query = $trashed ? $this->sizes->trashedQuery() : $this->sizes->query();

        return $query->when($keyword !== '', fn ($query) => $query->where('name', 'like', '%' . $keyword . '%'));
    }
}
