<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ColorRepositoryInterface;
use App\Models\Color;
use Illuminate\Database\Eloquent\Builder;
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
