<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


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
            if (isset($data['image']) && $data['image']->isValid()) {
                $data['image'] = $data['image']->store('variants', 'public');
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

        if (isset($data['image']) && $data['image']->isValid()) {
            // Xoá ảnh cũ nếu có
            if ($variant->image) {
                Storage::disk('public')->delete($variant->image);
            }
            $data['image'] = $data['image']->store('variants', 'public');
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

            // Nếu biến thể đã từng có trong OrderDetail -> chỉ soft delete
            if (OrderDetail::where('variant_id', $id)->exists()) {
                $variant->delete();
                return true;
            }

            // Nếu chưa có trong đơn hàng -> vẫn chỉ soft delete
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

                // Nếu đã tồn tại trong OrderDetail thì KHÔNG được xoá vĩnh viễn
                if (OrderDetail::where('variant_id', $id)->exists()) {
                    return false;
                }

                // Xoá file ảnh nếu có
                if ($variant->image) {
                    Storage::disk('public')->delete($variant->image);
                }

                // Xoá cứng record
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
                    // Nếu đã từng có trong đơn hàng -> chỉ soft delete
                    if (OrderDetail::where('variant_id', $id)->exists()) {
                        $variant->delete();
                    } else {
                        $variant->delete();
                    }
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
