<?php

namespace App\Services\Admin\Banners;

use App\Contracts\Repositories\BannerRepositoryInterface;
use App\Models\Banner;
use App\Support\Pagination;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class AdminBannerService
{
    public function __construct(protected BannerRepositoryInterface $banners) {}

    public function indexData(?string $search = null): array
    {
        $banners = $this->banners->paginatedForAdmin($search);

        $banners = Pagination::withQueryString($banners);

        return compact('banners');
    }

    public function trashData(): array
    {
        return [
            'banners' => $this->banners->trashedPaginatedForAdmin(),
        ];
    }

    public function find(int $id): Banner
    {
        return $this->banners->find($id);
    }

    public function create(array $data, ?UploadedFile $image = null, bool $isActive = false): void
    {
        if ($image) {
            $data['image'] = $image->store('banner', 'public');
        }

        $data['is_active'] = $isActive;

        $this->banners->create($data);
    }

    public function update(int $id, array $data, ?UploadedFile $image = null, bool $isActive = false): void
    {
        $banner = $this->banners->find($id);
        unset($data['image']);

        $currentImage = $banner->image;
        $newImagePath = null;

        if ($image) {
            $newImagePath = $image->store('banner', 'public');
            $data['image'] = $newImagePath;
        }

        $data['is_active'] = $isActive;

        if (! $this->banners->update($banner, $data)) {
            throw new RuntimeException('Cập nhật không thành công!');
        }

        if ($newImagePath && $currentImage && Storage::disk('public')->exists($currentImage)) {
            Storage::disk('public')->delete($currentImage);
        }
    }

    public function softDelete(int $id): void
    {
        $this->banners->delete($this->banners->find($id));
    }

    public function bulkDelete(array $ids): void
    {
        if (empty($ids)) {
            throw new RuntimeException('Danh sách banner không hợp lệ');
        }

        $this->banners->softDeleteMany($ids);
    }

    public function restore(int $id): void
    {
        $this->banners->restore($this->banners->findTrashed($id));
    }
}
