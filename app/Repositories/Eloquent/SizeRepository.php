<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\SizeRepositoryInterface;
use App\Models\Size;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SizeRepository implements SizeRepositoryInterface
{
    public function query(): Builder
    {
        return Size::query();
    }

    public function trashedQuery(): Builder
    {
        return Size::onlyTrashed();
    }

    public function find(int $id): Size
    {
        return Size::findOrFail($id);
    }

    public function findTrashed(int $id): Size
    {
        return Size::onlyTrashed()->findOrFail($id);
    }

    public function create(array $data): Size
    {
        return Size::create($data);
    }

    public function activeNameExists(string $name): bool
    {
        return Size::where('name', $name)->exists();
    }

    public function trashedNameExists(string $name, ?int $exceptId = null): bool
    {
        return Size::onlyTrashed()
            ->where('name', $name)
            ->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))
            ->exists();
    }

    public function variantUsageCount(int $sizeId): int
    {
        return DB::table('product_variants')->where('id_size', $sizeId)->count();
    }
}
