<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ColorRepositoryInterface;
use App\Models\Color;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ColorRepository implements ColorRepositoryInterface
{
    public function query(): Builder
    {
        return Color::query();
    }

    public function trashedQuery(): Builder
    {
        return Color::onlyTrashed();
    }

    public function all(): Collection
    {
        return Color::all();
    }

    public function paginatedForAdmin(string $keyword = '', bool $trashed = false, int $perPage = 10): LengthAwarePaginator
    {
        $query = $trashed ? Color::onlyTrashed() : Color::query();

        return $query
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('name', 'like', '%'.$keyword.'%')
                        ->orWhere('code', 'like', '%'.$keyword.'%');
                });
            })
            ->when($trashed, fn ($query) => $query->latest('deleted_at'), fn ($query) => $query->latest('id'))
            ->paginate($perPage);
    }

    public function find(int $id): Color
    {
        return Color::findOrFail($id);
    }

    public function findTrashed(int $id): Color
    {
        return Color::onlyTrashed()->findOrFail($id);
    }

    public function create(array $data): Color
    {
        return Color::create($data);
    }

    public function update(Color $color, array $data): bool
    {
        return $color->update($data);
    }

    public function delete(Color $color): bool
    {
        return (bool) $color->delete();
    }

    public function restore(Color $color): bool
    {
        return (bool) $color->restore();
    }

    public function forceDelete(Color $color): bool
    {
        return (bool) $color->forceDelete();
    }

    public function forceDeleteTrashed(): int
    {
        return Color::onlyTrashed()->forceDelete();
    }

    public function trashedDuplicate(string $name, string $code, ?int $exceptId = null): ?Color
    {
        return Color::onlyTrashed()
            ->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))
            ->where(fn ($query) => $query->where('name', $name)->orWhere('code', $code))
            ->first();
    }

    public function activeDuplicateFor(Color $color): ?Color
    {
        return Color::query()
            ->where(fn ($query) => $query->where('name', $color->name)->orWhere('code', $color->code))
            ->first();
    }

    public function variantUsageCount(int $colorId): int
    {
        return DB::table('product_variants')->where('id_color', $colorId)->count();
    }

    public function trashedBlockedByVariantsCount(): int
    {
        return Color::onlyTrashed()
            ->whereIn('id', function ($query) {
                $query->select('id_color')->from('product_variants')->whereNotNull('id_color');
            })
            ->count();
    }
}
