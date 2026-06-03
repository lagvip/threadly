<?php

namespace App\Services\Admin\Colors;

use App\Contracts\Repositories\ColorRepositoryInterface;

class AdminColorQueryService
{
    public function __construct(protected ColorRepositoryInterface $colors)
    {
    }

    public function indexData(string $keyword): array
    {
        return [
            'colors' => $this->filteredQuery($keyword)->latest('id')->paginate(10)->withQueryString(),
            'keyword' => $keyword,
        ];
    }

    public function binData(string $keyword): array
    {
        return [
            'colors' => $this->filteredQuery($keyword, true)->latest('deleted_at')->paginate(10)->withQueryString(),
            'keyword' => $keyword,
        ];
    }

    protected function filteredQuery(string $keyword, bool $trashed = false)
    {
        $query = $trashed ? $this->colors->trashedQuery() : $this->colors->query();

        return $query->when($keyword !== '', function ($query) use ($keyword) {
            $query->where(function ($subQuery) use ($keyword) {
                $subQuery->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('code', 'like', '%' . $keyword . '%');
            });
        });
    }
}
