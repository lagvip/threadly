<?php

namespace App\Services;

use App\Contracts\Repositories\OrderDetailRepositoryInterface;
use App\Contracts\Repositories\ProductVariantRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ProductVariantService
{
    public function __construct(
        protected ProductVariantRepositoryInterface $variants,
        protected OrderDetailRepositoryInterface $orderDetails,
    ) {
    }

    public function getProductVariants()
    {
        return $this->variants->allWithRelations();
    }

    public function getProductVariantsById($id)
    {
        return $this->variants->findWithRelations((int) $id);
    }

    public function createProductVariant($data)
    {
        try {
            if (isset($data['image']) && $data['image'] instanceof UploadedFile && $data['image']->isValid()) {
                $data['image'] = $data['image']->store('variants', 'public');
            }

            if (!isset($data['status']) || empty($data['status'])) {
                $data['status'] = 'active';
            }

            if (!isset($data['price']) || $data['price'] === null || $data['price'] === '') {
                $data['price'] = 0;
            }

            if (!isset($data['quantity']) || $data['quantity'] === null || $data['quantity'] === '') {
                $data['quantity'] = 0;
            }

            return $this->variants->create($data);
        } catch (\Exception $e) {
            Log::error('Lỗi khi tạo biến thể sản phẩm: ' . $e->getMessage());
            return false;
        }
    }

    public function updateProductVariant($data, $id)
    {
        try {
            $variant = $this->variants->findWithProduct((int) $id);

            if (isset($data['status']) && $data['status'] === 'active' && $variant->product && $variant->product->status !== 'active') {
                throw new \Exception('Không thể kích hoạt biến thể khi sản phẩm cha đang không hoạt động.');
            }

            if (isset($data['image']) && $data['image'] instanceof UploadedFile && $data['image']->isValid()) {
                if ($variant->image) {
                    Storage::disk('public')->delete($variant->image);
                }

                $data['image'] = $data['image']->store('variants', 'public');
            } else {
                unset($data['image']);
            }

            if (!isset($data['price']) || $data['price'] === null || $data['price'] === '') {
                $data['price'] = 0;
            }

            if (!isset($data['quantity']) || $data['quantity'] === null || $data['quantity'] === '') {
                $data['quantity'] = 0;
            }

            if (!isset($data['status']) || empty($data['status'])) {
                $data['status'] = $variant->status ?? 'active';
            }

            $variant->update($data);

            return $variant;
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật biến thể sản phẩm: ' . $e->getMessage());
            return false;
        }
    }

    public function updateStatus($id, $status)
    {
        try {
            $variant = $this->variants->findWithProduct((int) $id);
            // Nếu đang kích hoạt biến thể nhưng sản phẩm cha không hoạt động, trả về lỗi
            if ($status === 'active' && $variant->product && $variant->product->status !== 'active') {
                return false;
            }

            $variant->status = $status;
            $variant->save();

            return true;
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật trạng thái biến thể sản phẩm: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteProductVariant($id)
    {
        try {
            $variant = $this->variants->find((int) $id);

            if ($this->orderDetails->existsForVariant((int) $id)) {
                $variant->delete();
                return true;
            }

            $variant->delete();
            return true;
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa biến thể sản phẩm: ' . $e->getMessage());
            return false;
        }
    }

    public function getTrashedProductVariants()
    {
        return $this->variants->trashedWithRelations();
    }

    public function restoreProductVariant($id)
    {
        try {
            $variant = $this->variants->findTrashed((int) $id);
            $variant->restore();
            return true;
        } catch (\Exception $e) {
            Log::error('Lỗi khi khôi phục biến thể sản phẩm: ' . $e->getMessage());
            return false;
        }
    }

    public function forceDeleteProductVariant($id)
    {
        try {
            $variant = $this->variants->findTrashed((int) $id);

            if ($this->orderDetails->existsForVariant((int) $id)) {
                return false;
            }

            if ($variant->image) {
                Storage::disk('public')->delete($variant->image);
            }

            $variant->forceDelete();
            return true;
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa vĩnh viễn biến thể sản phẩm: ' . $e->getMessage());
            return false;
        }
    }

    public function bulkDelete(array $ids)
    {
        try {
            foreach ($ids as $id) {
                $variant = $this->variants->query()->find($id);
                if ($variant) {
                    $variant->delete();
                }
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa hàng loạt biến thể sản phẩm: ' . $e->getMessage());
            return false;
        }
    }

    public function bulkRestore(array $ids)
    {
        try {
            $this->variants->restoreMany($ids);
            return true;
        } catch (\Exception $e) {
            Log::error('Lỗi khi khôi phục hàng loạt biến thể sản phẩm: ' . $e->getMessage());
            return false;
        }
    }
}
