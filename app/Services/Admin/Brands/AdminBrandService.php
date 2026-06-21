<?php

namespace App\Services\Admin\Brands;

use App\Contracts\Repositories\BrandRepositoryInterface;
use App\Models\Brand;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class AdminBrandService
{
    public function __construct(protected BrandRepositoryInterface $brands) {}

    public function indexData(): array
    {
        return ['brands' => $this->brands->all()];
    }

    public function trashData(): array
    {
        return ['trashedBrands' => $this->brands->trashed()];
    }

    public function find(int $id): Brand
    {
        return $this->brands->find($id);
    }

    public function create(array $data, ?UploadedFile $image = null): void
    {
        $newImagePath = null;

        if ($image) {
            $newImagePath = $image->store('brands', 'public');

            if (! $newImagePath) {
                throw new RuntimeException('Không thể lưu ảnh thương hiệu.');
            }

            $data['image'] = $newImagePath;
        }

        try {
            $this->brands->create($data);
        } catch (\Throwable $e) {
            $this->deletePath($newImagePath);

            throw $e;
        }
    }

    public function update(int $id, array $data, ?UploadedFile $image = null): void
    {
        $brand = $this->brands->find($id);

        $oldImagePath = $brand->image;
        $newImagePath = $image?->store('brands', 'public') ?: null;

        if ($image && ! $newImagePath) {
            throw new RuntimeException('Không thể lưu ảnh thương hiệu.');
        }

        if ($newImagePath) {
            $data['image'] = $newImagePath;
        }

        try {
            DB::transaction(function () use ($brand, $data, $newImagePath, $oldImagePath) {
                if (! $this->brands->update($brand, $data)) {
                    throw new RuntimeException('Cập nhật thương hiệu không thành công.');
                }

                if ($newImagePath) {
                    DB::afterCommit(fn () => $this->deletePath($oldImagePath));
                }
            });
        } catch (\Throwable $e) {
            $this->deletePath($newImagePath);

            throw $e;
        }
    }

    public function softDelete(int $id): void
    {
        $brand = $this->brands->find($id);

        if ($brand->products()->withTrashed()->exists()) {
            throw new RuntimeException('Không thể xóa thương hiệu vì đang có sản phẩm sử dụng.');
        }

        $this->brands->delete($brand);
    }

    public function restore(int $id): void
    {
        $this->brands->restore($this->brands->findWithTrashed($id));
    }

    public function forceDelete(int $id): void
    {
        $brand = $this->brands->findWithTrashed($id);

        if ($brand->products()->withTrashed()->exists()) {
            throw new RuntimeException('Không thể xóa vĩnh viễn thương hiệu vì đang có sản phẩm sử dụng.');
        }

        DB::transaction(function () use ($brand) {
            $imagePath = $brand->image;
            $this->brands->forceDelete($brand);
            DB::afterCommit(fn () => $this->deletePath($imagePath));
        });
    }

    protected function deletePath(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
