<?php

namespace App\Services\Admin\Colors;

use App\Contracts\Repositories\ColorRepositoryInterface;
use App\Models\Color;
use RuntimeException;

class AdminColorService
{
    public function __construct(protected ColorRepositoryInterface $colors) {}

    public function create(array $data): ?string
    {
        if ($this->colors->trashedDuplicate($data['name'], $data['code'])) {
            return 'Màu này hoặc mã màu này đang nằm trong thùng rác. Hãy khôi phục thay vì tạo mới.';
        }

        $this->colors->create([
            'name' => $data['name'],
            'code' => $data['code'],
        ]);

        return null;
    }

    public function update(int $id, array $data): ?string
    {
        $color = $this->colors->find($id);

        if ($this->colors->trashedDuplicate($data['name'], $data['code'], $color->id)) {
            return 'Đã có một màu hoặc mã màu trùng trong thùng rác. Hãy xử lý bản cũ trước.';
        }

        $this->colors->update($color, [
            'name' => $data['name'],
            'code' => $data['code'],
        ]);

        return null;
    }

    public function find(int $id): Color
    {
        return $this->colors->find($id);
    }

    public function softDelete(int $id): void
    {
        $this->colors->delete($this->colors->find($id));
    }

    public function restore(int $id): void
    {
        $color = $this->colors->findTrashed($id);

        if ($this->colors->activeDuplicateFor($color)) {
            throw new RuntimeException('Không thể khôi phục vì đã có màu hoặc mã màu trùng đang hoạt động.');
        }

        $this->colors->restore($color);
    }

    public function forceDelete(int $id): void
    {
        $color = $this->colors->findTrashed($id);

        if ($this->colors->variantUsageCount($color->id) > 0) {
            throw new RuntimeException('Không thể xóa vĩnh viễn vì màu này vẫn đang được dùng trong biến thể sản phẩm.');
        }

        $this->colors->forceDelete($color);
    }

    public function forceDeleteAll(): void
    {
        if ($this->colors->trashedBlockedByVariantsCount() > 0) {
            throw new RuntimeException('Có màu trong thùng rác vẫn đang được dùng trong biến thể sản phẩm, không thể xóa tất cả.');
        }

        $this->colors->forceDeleteTrashed();
    }
}
