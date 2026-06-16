<?php

namespace App\Services\Admin\Sizes;

use App\Contracts\Repositories\SizeRepositoryInterface;
use App\Models\Size;
use RuntimeException;

class AdminSizeService
{
    public function __construct(protected SizeRepositoryInterface $sizes) {}

    public function create(array $data): ?string
    {
        if ($this->sizes->trashedNameExists($data['name'])) {
            return 'Size này đang nằm trong thùng rác. Hãy khôi phục thay vì tạo mới.';
        }

        $this->sizes->create(['name' => $data['name']]);

        return null;
    }

    public function update(int $id, array $data): ?string
    {
        $size = $this->sizes->find($id);

        if ($this->sizes->trashedNameExists($data['name'], $size->id)) {
            return 'Đã có một size cùng tên trong thùng rác. Hãy khôi phục hoặc xóa vĩnh viễn bản cũ trước.';
        }

        $this->sizes->update($size, ['name' => $data['name']]);

        return null;
    }

    public function find(int $id): Size
    {
        return $this->sizes->find($id);
    }

    public function softDelete(int $id): void
    {
        $this->sizes->delete($this->sizes->find($id));
    }

    public function restore(int $id): void
    {
        $size = $this->sizes->findTrashed($id);

        if ($this->sizes->activeNameExists($size->name)) {
            throw new RuntimeException('Không thể khôi phục vì đã có size cùng tên đang hoạt động.');
        }

        $this->sizes->restore($size);
    }

    public function forceDelete(int $id): void
    {
        $size = $this->sizes->findTrashed($id);

        if ($this->sizes->variantUsageCount($size->id) > 0) {
            throw new RuntimeException('Không thể xóa vĩnh viễn vì size này vẫn đang được dùng trong biến thể sản phẩm.');
        }

        $this->sizes->forceDelete($size);
    }
}
