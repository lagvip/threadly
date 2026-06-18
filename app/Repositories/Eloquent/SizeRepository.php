<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\SizeRepositoryInterface;
use App\Models\Size;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SizeRepository implements SizeRepositoryInterface
{
    protected function query(): Builder
    {
        return Size::query();
    }

    protected function trashedQuery(): Builder
    {
        return Size::onlyTrashed();
    }

    public function all(): Collection
    {
        return Size::all();
    }

    public function paginatedForAdmin(string $keyword = '', bool $trashed = false, int $perPage = 10): LengthAwarePaginator
    {
        $query = $trashed ? Size::onlyTrashed() : Size::query();

        return $query
            ->when($keyword !== '', fn ($query) => $query->where('name', 'like', '%'.$keyword.'%'))
            ->when($trashed, fn ($query) => $query->latest('deleted_at'), fn ($query) => $query->latest())
            ->paginate($perPage);
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

    public function update(Size $size, array $data): bool
    {
        return $size->update($data);
    }

    public function delete(Size $size): bool
    {
        return (bool) $size->delete();
    }

    public function restore(Size $size): bool
    {
        return (bool) $size->restore();
    }

    public function forceDelete(Size $size): bool
    {
        return (bool) $size->forceDelete();
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
