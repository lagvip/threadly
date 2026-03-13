<?php

namespace App\Services;

use App\Models\Product;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

class ProductService
{
    public function getAllProducts()
    {
        return Product::with(['brand', 'category'])->paginate(10);
    }

    public function getProductById($id)
    {
        return Product::with(['brand', 'category', 'variants.color', 'variants.size'])
            ->findOrFail($id);
    }

    public function createProduct($data)
    {
        try {
            if (isset($data['image_primary']) && $data['image_primary'] instanceof UploadedFile) {
                $data['image_primary'] = $data['image_primary']->store('products', 'public');
            }

            unset($data['variants']);

            return Product::create($data);
        } catch (\Exception $e) {
            Log::error('Lỗi khi tạo sản phẩm: ' . $e->getMessage());
            return false;
        }
    }

    public function updateProduct($data, $id)
    {
        DB::beginTransaction();

        try {
            $product = Product::findOrFail($id);

            if (isset($data['image_primary']) && $data['image_primary'] instanceof UploadedFile) {
                if ($product->image_primary) {
                    Storage::disk('public')->delete($product->image_primary);
                }
                $data['image_primary'] = $data['image_primary']->store('products', 'public');
            } else {
                unset($data['image_primary']);
            }

            $product->update($data);

            if (!empty($data['variants']) && is_array($data['variants'])) {
                foreach ($data['variants'] as $variantData) {
                    if (empty($variantData['id'])) {
                        continue;
                    }

                    if (!empty($variantData['delete']) && (int) $variantData['delete'] === 1) {
                        app(ProductVariantService::class)->deleteProductVariant($variantData['id']);
                        continue;
                    }

                    app(ProductVariantService::class)->updateProductVariant($variantData, $variantData['id']);
                }
            }

            if (!empty($data['variants_new']) && is_array($data['variants_new'])) {
                foreach ($data['variants_new'] as $variantNew) {
                    $variantNew['id_product'] = $product->id;
                    $variantNew['price'] = $variantNew['price'] ?? 0;
                    $variantNew['quantity'] = $variantNew['quantity'] ?? 0;
                    $variantNew['status'] = $variantNew['status'] ?? 'active';

                    app(ProductVariantService::class)->createProductVariant($variantNew);
                }
            }

            DB::commit();
            return $product;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi cập nhật sản phẩm: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteProduct($id)
    {
        try {
            $product = Product::findOrFail($id);

            if (OrderDetail::where('product_id', $id)->exists()) {
                $product->delete();
                return true;
            }

            $product->delete();
            return true;
        } catch (\Exception $e) {
            Log::error('Lỗi khi xoá sản phẩm: ' . $e->getMessage());
            return false;
        }
    }

    public function getTrashedProducts()
    {
        return Product::onlyTrashed()->with(['brand', 'category'])->get();
    }

    public function restoreProduct($id)
    {
        try {
            $product = Product::onlyTrashed()->findOrFail($id);
            $product->restore();
            return true;
        } catch (\Exception $e) {
            Log::error('Lỗi khi khôi phục sản phẩm: ' . $e->getMessage());
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $product = Product::onlyTrashed()->findOrFail($id);

            if (OrderDetail::where('product_id', $id)->exists()) {
                return false;
            }

            if ($product->image_primary) {
                Storage::disk('public')->delete($product->image_primary);
            }

            $product->forceDelete();
            return true;
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa vĩnh viễn sản phẩm: ' . $e->getMessage());
            return false;
        }
    }

    public function bulkDelete(array $ids)
    {
        try {
            foreach ($ids as $id) {
                $product = Product::find($id);
                if ($product) {
                    $product->delete();
                }
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa hàng loạt sản phẩm: ' . $e->getMessage());
            return false;
        }
    }

    public function bulkRestore(array $ids)
    {
        try {
            Product::onlyTrashed()->whereIn('id', $ids)->restore();
            return true;
        } catch (\Exception $e) {
            Log::error('Lỗi khi khôi phục hàng loạt sản phẩm: ' . $e->getMessage());
            return false;
        }
    }

    public function getProductsByCategory($categoryId)
    {
        return Product::with(['variants.color', 'variants.size'])
            ->where('id_category', $categoryId)
            ->where('status', 'active')
            ->whereHas('variants', function ($query) {
                $query->where('price', '>', 0);
            });
    }
}
