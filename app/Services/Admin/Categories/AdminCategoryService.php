<?php

namespace App\Services\Admin\Categories;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class AdminCategoryService
{
    public function __construct(protected CategoryRepositoryInterface $categories) {}

    public function create(array $data, UploadedFile $image): void
    {
        $this->assertValidParent($data['id_parent'] ?? null);
        $newImagePath = Storage::disk('public')->putFile('category', $image);

        if (! $newImagePath) {
            throw new RuntimeException('Không thể lưu ảnh danh mục.');
        }

        try {
            $this->categories->create([
                'name' => $data['name'],
                'id_parent' => ! empty($data['id_parent']) ? $data['id_parent'] : null,
                'image' => $newImagePath,
            ]);
        } catch (\Throwable $e) {
            $this->deletePath($newImagePath);

            throw $e;
        }
    }

    public function update(int $id, array $data, ?UploadedFile $image = null): void
    {
        $category = $this->categories->find($id);
        $parentId = $data['id_parent'] ?? null;

        $this->assertValidParent($parentId, $category);

        $payload = [
            'name' => $data['name'],
            'id_parent' => ! empty($parentId) ? $parentId : null,
        ];

        $currentImage = $category->image;
        $newImagePath = null;

        if ($image) {
            $newImagePath = Storage::disk('public')->putFile('category', $image);

            if (! $newImagePath) {
                throw new RuntimeException('Không thể lưu ảnh danh mục.');
            }

            $payload['image'] = $newImagePath;
        }

        try {
            DB::transaction(function () use ($category, $payload, $newImagePath, $currentImage) {
                if (! $this->categories->update($category, $payload)) {
                    throw new RuntimeException('Sửa không thành công!');
                }

                if ($newImagePath) {
                    DB::afterCommit(fn () => $this->deletePath($currentImage));
                }
            });
        } catch (\Throwable $e) {
            $this->deletePath($newImagePath);

            throw $e;
        }
    }

    public function softDelete(int $id): void
    {
        $category = $this->categories->find($id);

        if ($this->categories->hasChildren($category)) {
            throw new RuntimeException('Không thể xoá danh mục đang có danh mục con.');
        }

        if ($this->categories->hasProducts($category)) {
            throw new RuntimeException('Không thể xoá danh mục đang có sản phẩm.');
        }

        $this->categories->delete($category);
    }

    public function restore(int $id): void
    {
        $this->categories->restore($this->categories->findWithTrashed($id));
    }

    public function forceDelete(int $id): void
    {
        $category = $this->categories->findWithTrashed($id);

        DB::transaction(function () use ($category) {
            $imagePath = $category->image;
            $this->categories->forceDelete($category);
            DB::afterCommit(fn () => $this->deletePath($imagePath));
        });
    }

    protected function assertValidParent($parentId, ?Category $category = null): void
    {
        if (empty($parentId)) {
            return;
        }

        if ($category && (int) $parentId === (int) $category->id) {
            throw new RuntimeException('Danh mục không thể là cha của chính nó.');
        }

        if ($category && $this->categories->hasChildren($category)) {
            throw new RuntimeException('Danh mục đang là danh mục cha, không thể chuyển thành danh mục con.');
        }

        $parent = $this->categories->find($parentId);

        if (! is_null($parent->id_parent)) {
            throw new RuntimeException('Chỉ được chọn danh mục gốc làm danh mục cha.');
        }
    }

    protected function deletePath(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
