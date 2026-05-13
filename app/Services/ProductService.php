<?php

namespace App\Services;

use App\Models\Product;
use App\Models\OrderDetail;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;

class ProductService
{
    public function getAllProducts(array $filters = [])
    {
        $query = Product::with(['brand', 'category']);

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . trim($filters['search']) . '%');
        }

        if (!empty($filters['brand_id'])) {
            $query->where('id_brand', $filters['brand_id']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('id_category', $filters['category_id']);
        }

        return $query->latest('created_at')->paginate(10);
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

            $payload = $data instanceof Request ? $data->all() : $data;
            $request = $data instanceof Request ? $data : request();

            if (isset($payload['image_primary']) && $payload['image_primary'] instanceof UploadedFile) {
                if ($product->image_primary) {
                    Storage::disk('public')->delete($product->image_primary);
                }
                $payload['image_primary'] = $payload['image_primary']->store('products', 'public');
            } else {
                unset($payload['image_primary']);
            }

            unset($payload['variants'], $payload['variants_new']);

            $product->update($payload);

            $variantService = app(ProductVariantService::class);
            $usedCombinations = [];

            if (!empty($request->input('variants')) && is_array($request->input('variants'))) {
                foreach ($request->input('variants') as $index => $variantData) {
                    if (empty($variantData['id'])) {
                        continue;
                    }

                    $variant = ProductVariant::where('id', $variantData['id'])
                        ->where('id_product', $product->id)
                        ->first();

                    if (!$variant) {
                        continue;
                    }

                    $delete = (int)($variantData['delete'] ?? 0);

                    if ($delete === 1) {
                        $variantService->deleteProductVariant($variant->id);
                        continue;
                    }

                    $key = $variantData['id_color'] . '-' . $variantData['id_size'];

                    if (in_array($key, $usedCombinations)) {
                        throw new \Exception('Biến thể bị trùng màu sắc và kích cỡ.');
                    }

                    $usedCombinations[] = $key;

                    $updateData = [
                        'id_color' => $variantData['id_color'],
                        'id_size' => $variantData['id_size'],
                        'price' => $variantData['price'] ?? 0,
                        'quantity' => $variantData['quantity'] ?? 0,
                        'status' => $variantData['status'] ?? ($variant->status ?? 'active'),
                    ];

                    if ($request->hasFile("variants.$index.image")) {
                        $updateData['image'] = $request->file("variants.$index.image");
                    }

                    $updated = $variantService->updateProductVariant($updateData, $variant->id);

                    if (!$updated) {
                        throw new \Exception('Không thể cập nhật biến thể sản phẩm.');
                    }
                }
            }

            if (!empty($request->input('variants_new')) && is_array($request->input('variants_new'))) {
                foreach ($request->input('variants_new') as $index => $variantNew) {
                    $key = $variantNew['id_color'] . '-' . $variantNew['id_size'];

                    if (in_array($key, $usedCombinations)) {
                        throw new \Exception('Biến thể mới bị trùng màu sắc và kích cỡ.');
                    }

                    $exists = ProductVariant::where('id_product', $product->id)
                        ->where('id_color', $variantNew['id_color'])
                        ->where('id_size', $variantNew['id_size'])
                        ->whereNull('deleted_at')
                        ->exists();

                    if ($exists) {
                        throw new \Exception('Biến thể mới đã tồn tại.');
                    }

                    $usedCombinations[] = $key;

                    $newData = [
                        'id_product' => $product->id,
                        'id_color' => $variantNew['id_color'],
                        'id_size' => $variantNew['id_size'],
                        'price' => $variantNew['price'] ?? 0,
                        'quantity' => $variantNew['quantity'] ?? 0,
                        'status' => $variantNew['status'] ?? 'active',
                    ];

                    if ($request->hasFile("variants_new.$index.image")) {
                        $newData['image'] = $request->file("variants_new.$index.image");
                    }

                    $created = $variantService->createProductVariant($newData);

                    if (!$created) {
                        throw new \Exception('Không thể tạo biến thể mới.');
                    }
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

    public function updateStatus($id, $status)
    {
        try {
            $product = Product::findOrFail($id);
            $product->status = $status;
            $product->save();

            return true;
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật trạng thái sản phẩm: ' . $e->getMessage());
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
                $query->where('price', '>', 0)
                    ->where('status', 'active');
            });
    }
}
