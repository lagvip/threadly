<?php

namespace App\Services\Admin\Products;

use App\Contracts\Repositories\OrderDetailRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\ProductVariantRepositoryInterface;
use App\Enums\ProductStatus;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdminProductService
{
    public function __construct(
        protected ProductRepositoryInterface $products,
        protected ProductVariantRepositoryInterface $variants,
        protected OrderDetailRepositoryInterface $orderDetails,
        protected AdminProductVariantService $variantService,
    ) {}

    public function getAllProducts(array $filters = [])
    {
        return $this->products->paginateForAdmin($filters, 10);
    }

    public function getProductById($id)
    {
        return $this->products->findWithAdminDetail((int) $id);
    }

    public function createProduct($data)
    {
        $newPrimaryImagePath = null;

        try {
            if (isset($data['image_primary']) && $data['image_primary'] instanceof UploadedFile) {
                $newPrimaryImagePath = $data['image_primary']->store('products', 'public');
                $data['image_primary'] = $newPrimaryImagePath;
            }

            unset($data['variants']);

            try {
                return DB::transaction(fn () => $this->products->create($data));
            } catch (\Throwable $e) {
                if ($newPrimaryImagePath) {
                    Storage::disk('public')->delete($newPrimaryImagePath);
                }

                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Lỗi khi tạo sản phẩm: '.$e->getMessage());

            return false;
        }
    }

    public function updateProduct(array $data, $id, array $variantImageFiles = [], array $newVariantImageFiles = [])
    {
        $newPrimaryImagePath = null;
        $newVariantImagePaths = [];

        try {
            $product = $this->products->find((int) $id);
            $payload = $data;
            $oldPrimaryImagePath = $product->image_primary;

            if (isset($payload['image_primary']) && $payload['image_primary'] instanceof UploadedFile) {
                $newPrimaryImagePath = $payload['image_primary']->store('products', 'public');
                $payload['image_primary'] = $newPrimaryImagePath;
            } else {
                unset($payload['image_primary']);
            }

            try {
                return DB::transaction(function () use ($data, $variantImageFiles, $newVariantImageFiles, $product, $payload, $oldPrimaryImagePath, $newPrimaryImagePath, &$newVariantImagePaths) {
                    unset($payload['variants'], $payload['variants_new']);

                    $this->products->update($product, $payload);

                    if ($newPrimaryImagePath && $oldPrimaryImagePath && $oldPrimaryImagePath !== $newPrimaryImagePath) {
                        DB::afterCommit(fn () => Storage::disk('public')->delete($oldPrimaryImagePath));
                    }

                    $usedCombinations = [];

                    if (! empty($data['variants']) && is_array($data['variants'])) {
                        foreach ($data['variants'] as $index => $variantData) {
                            if (empty($variantData['id'])) {
                                continue;
                            }

                            $variant = $this->variants->findForProduct((int) $variantData['id'], (int) $product->id);

                            if (! $variant) {
                                continue;
                            }

                            $delete = (int) ($variantData['delete'] ?? 0);

                            if ($delete === 1) {
                                $this->variantService->deleteProductVariant($variant->id);

                                continue;
                            }

                            $key = $variantData['id_color'].'-'.$variantData['id_size'];

                            if (in_array($key, $usedCombinations)) {
                                throw new \Exception('Biến thể bị trùng màu sắc và kích cỡ.');
                            }

                            $usedCombinations[] = $key;

                            $updateData = [
                                'id_color' => $variantData['id_color'],
                                'id_size' => $variantData['id_size'],
                                'price' => $variantData['price'] ?? 0,
                                'quantity' => $variantData['quantity'] ?? 0,
                                'status' => $variantData['status'] ?? ($variant->status ?? ProductStatus::Active->value),
                            ];

                            if (isset($variantImageFiles[$index]['image'])) {
                                $updateData['image'] = $variantImageFiles[$index]['image'];
                            }

                            $updated = $this->variantService->updateProductVariant($updateData, $variant->id);

                            if (! $updated) {
                                throw new \Exception('Không thể cập nhật biến thể sản phẩm.');
                            }

                            if (isset($variantImageFiles[$index]['image']) && $updated->image) {
                                $newVariantImagePaths[] = $updated->image;
                            }
                        }
                    }

                    if (! empty($data['variants_new']) && is_array($data['variants_new'])) {
                        foreach ($data['variants_new'] as $index => $variantNew) {
                            $key = $variantNew['id_color'].'-'.$variantNew['id_size'];

                            if (in_array($key, $usedCombinations)) {
                                throw new \Exception('Biến thể mới bị trùng màu sắc và kích cỡ.');
                            }

                            $exists = $this->variants->existsActiveCombination(
                                (int) $product->id,
                                (int) $variantNew['id_color'],
                                (int) $variantNew['id_size']
                            );

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
                                'status' => $variantNew['status'] ?? ProductStatus::Active->value,
                            ];

                            if (isset($newVariantImageFiles[$index]['image'])) {
                                $newData['image'] = $newVariantImageFiles[$index]['image'];
                            }

                            $created = $this->variantService->createProductVariant($newData);

                            if (! $created) {
                                throw new \Exception('Không thể tạo biến thể mới.');
                            }

                            if (isset($newVariantImageFiles[$index]['image']) && $created->image) {
                                $newVariantImagePaths[] = $created->image;
                            }
                        }
                    }

                    return $product;
                });
            } catch (\Exception $e) {
                if ($newPrimaryImagePath) {
                    Storage::disk('public')->delete($newPrimaryImagePath);
                }

                foreach (array_unique($newVariantImagePaths) as $newVariantImagePath) {
                    Storage::disk('public')->delete($newVariantImagePath);
                }

                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật sản phẩm: '.$e->getMessage());

            return false;
        }
    }

    public function updateStatus($id, $status)
    {
        try {
            $product = $this->products->find((int) $id);
            $this->products->update($product, ['status' => $status]);

            return true;
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật trạng thái sản phẩm: '.$e->getMessage());

            return false;
        }
    }

    public function deleteProduct($id)
    {
        try {
            $product = $this->products->find((int) $id);

            if ($this->orderDetails->existsForProduct((int) $id)) {
                $this->products->delete($product);

                return true;
            }

            $this->products->delete($product);

            return true;
        } catch (\Exception $e) {
            Log::error('Lỗi khi xoá sản phẩm: '.$e->getMessage());

            return false;
        }
    }

    public function getTrashedProducts()
    {
        return $this->products->trashedForAdmin();
    }

    public function restoreProduct($id)
    {
        try {
            $product = $this->products->findTrashed((int) $id);
            $this->products->restore($product);

            return true;
        } catch (\Exception $e) {
            Log::error('Lỗi khi khôi phục sản phẩm: '.$e->getMessage());

            return false;
        }
    }

    public function delete($id)
    {
        try {
            $product = $this->products->findTrashed((int) $id);

            if ($this->orderDetails->existsForProduct((int) $id)) {
                return false;
            }

            $primaryImagePath = $product->image_primary;

            DB::transaction(function () use ($product, $primaryImagePath) {
                $this->products->forceDelete($product);

                if ($primaryImagePath) {
                    DB::afterCommit(fn () => Storage::disk('public')->delete($primaryImagePath));
                }
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa vĩnh viễn sản phẩm: '.$e->getMessage());

            return false;
        }
    }

    public function bulkDelete(array $ids)
    {
        try {
            foreach ($ids as $id) {
                $product = $this->products->findForAdminOrNull((int) $id);
                if ($product) {
                    $this->products->delete($product);
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa hàng loạt sản phẩm: '.$e->getMessage());

            return false;
        }
    }

    public function bulkRestore(array $ids)
    {
        try {
            $this->products->restoreMany($ids);

            return true;
        } catch (\Exception $e) {
            Log::error('Lỗi khi khôi phục hàng loạt sản phẩm: '.$e->getMessage());

            return false;
        }
    }

    public function getProductsByCategory($categoryId)
    {
        return $this->products->activeProductsForCategory((int) $categoryId);
    }
}
