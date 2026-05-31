<?php

namespace App\Services\Admin\Brands;

use App\Contracts\Repositories\BrandRepositoryInterface;
use App\Models\Brand;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class AdminBrandService
{
    public function __construct(protected BrandRepositoryInterface $brands)
    {
    }

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
        if ($image) {
            $data['image'] = $image->store('brands', 'public');
        }

        $this->brands->create($data);
    }

    public function update(int $id, array $data, ?UploadedFile $image = null): void
    {
        $brand = $this->brands->find($id);

        if ($image) {
            $this->deleteImage($brand);
            $data['image'] = $image->store('brands', 'public');
        }

        $brand->update($data);
    }

    public function softDelete(int $id): void
    {
        $brand = $this->brands->find($id);

        if ($brand->products()->withTrashed()->exists()) {
            throw new RuntimeException('Không thể xóa thương hiệu vì đang có sản phẩm sử dụng.');
        }

        $brand->delete();
    }

    public function restore(int $id): void
    {
        $this->brands->findWithTrashed($id)->restore();
    }

    public function forceDelete(int $id): void
    {
        $brand = $this->brands->findWithTrashed($id);

        if ($brand->products()->withTrashed()->exists()) {
            throw new RuntimeException('Không thể xóa vĩnh viễn thương hiệu vì đang có sản phẩm sử dụng.');
        }

        $this->deleteImage($brand);
        $brand->forceDelete();
    }

    protected function deleteImage(Brand $brand): void
    {
        if ($brand->image && Storage::disk('public')->exists($brand->image)) {
            Storage::disk('public')->delete($brand->image);
        }
    }
}
