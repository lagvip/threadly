<?php

namespace App\Services;

use App\Models\Product;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

class ProductService
{



    public function getAllProducts()
    {
        return Product::with(['brand', 'category'])->paginate(10);
    }


    // public function getProductById($id) //lấy sản phẩm theo id
    // {
    //     return Product::with(['brand', 'category'])->findOrFail($id);
    // }
    public function getProductById($id)
    {
        return Product::with(['brand', 'category', 'variants.color', 'variants.size'])
            ->findOrFail($id);
    }

    public function createProduct($data)
        {
            try {
                if (isset($data['image_primary'])) {
                    $data['image_primary'] = $data['image_primary']->store('products', 'public');
                }

                $product = Product::create($data);

                if (isset($data['variants'])) {
                    foreach ($data['variants'] as $variant) {
                        $variant['product_id'] = $product->id;
                        app(ProductVariantService::class)->createProductVariant($variant);
                    }
                }

                return $product;
            } catch (\Exception $e) {
                Log::error('Lỗi khi tạo sản phẩm: ' . $e->getMessage());
                return false;
            }
        }


    /**
     * Cập nhật sản phẩm.
     */
  public function updateProduct($data, $id)
    {
        DB::beginTransaction();
        try {
            $product = Product::findOrFail($id);

            // 1) Ảnh chính: chỉ xử lý khi có file upload mới
            if (isset($data['image_primary']) && $data['image_primary'] instanceof UploadedFile) {
                if ($product->image_primary) {
                    Storage::disk('public')->delete($product->image_primary);
                }
                $data['image_primary'] = $data['image_primary']->store('products', 'public');
            } else {
                unset($data['image_primary']); // tránh overwrite null
            }

            // 2) Cập nhật thông tin sản phẩm
            $product->update($data);

            // 3) Cập nhật hoặc xoá biến thể hiện có
            if (!empty($data['variants']) && is_array($data['variants'])) {
                foreach ($data['variants'] as $variantData) {
                    if (empty($variantData['id'])) continue;

                    // Nếu tick "xóa"
                    if (!empty($variantData['delete']) && (int)$variantData['delete'] === 1) {
                        app(ProductVariantService::class)->deleteProductVariant($variantData['id']);
                        continue;
                    }

                    // Update biến thể
                    app(ProductVariantService::class)->updateProductVariant($variantData, $variantData['id']);
                }
            }

            // 4) Thêm mới biến thể
            if (!empty($data['variants_new']) && is_array($data['variants_new'])) {
                foreach ($data['variants_new'] as $variantNew) {
                    $variantNew['product_id'] = $product->id;
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

        // Nếu đã tồn tại trong OrderDetail thì KHÔNG xoá cứng
        if (OrderDetail::where('product_id', $id)->exists()) {
            // Chỉ cho phép soft delete
            $product->delete();
            return true;
        }

        // Nếu chưa có trong đơn hàng -> vẫn soft delete bình thường
        $product->delete();
        return true;
    } catch (\Exception $e) {
        Log::error('Lỗi khi xoá sản phẩm: ' . $e->getMessage());
        return false;
    }
}



    public function getTrashedProducts() //danh sách đã xóa
    {
        return Product::onlyTrashed()->with(['brand', 'category'])->get();
    }

    public function restoreProduct($id) //khôi phục sản phẩm đã xóa
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


    public function delete($id) // xóa vĩnh viễn
    {
        try {
            $product = Product::onlyTrashed()->findOrFail($id);

            // Nếu sản phẩm đã có trong OrderDetail → KHÔNG được xoá vĩnh viễn
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
                    // Nếu có trong đơn hàng → chỉ soft delete
                    if (OrderDetail::where('product_id', $id)->exists()) {
                        $product->delete();
                    } else {
                        $product->delete(); // vẫn chỉ soft delete
                    }
                }
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa hàng loạt sản phẩm: ' . $e->getMessage());
            return false;
        }
    }



    public function bulkRestore(array $ids) //khôi phục nhiều bản ghi
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
