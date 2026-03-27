<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ProductVariantService
{
    public function getProductVariants()
    {
        return ProductVariant::with(['product', 'color', 'size'])->get();
    }

    public function getProductVariantsById($id)
    {
        return ProductVariant::with(['product', 'color', 'size'])->findOrFail($id);
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

            return ProductVariant::create($data);
        } catch (\Exception $e) {
            Log::error('Lỗi khi tạo biến thể sản phẩm: ' . $e->getMessage());
            return false;
        }
    }

    public function updateProductVariant($data, $id)
    {
        try {
            $variant = ProductVariant::findOrFail($id);

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

    public function deleteProductVariant($id)
    {
        try {
            $variant = ProductVariant::findOrFail($id);

            if (OrderDetail::where('variant_id', $id)->exists()) {
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
        return ProductVariant::onlyTrashed()->with(['product', 'color', 'size'])->get();
    }

    public function restoreProductVariant($id)
    {
        try {
            $variant = ProductVariant::onlyTrashed()->findOrFail($id);
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
            $variant = ProductVariant::onlyTrashed()->findOrFail($id);

            if (OrderDetail::where('variant_id', $id)->exists()) {
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
                $variant = ProductVariant::find($id);
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
            ProductVariant::onlyTrashed()->whereIn('id', $ids)->restore();
            return true;
        } catch (\Exception $e) {
            Log::error('Lỗi khi khôi phục hàng loạt biến thể sản phẩm: ' . $e->getMessage());
            return false;
        }
    }
}
