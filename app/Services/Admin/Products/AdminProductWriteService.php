<?php

namespace App\Services\Admin\Products;

use App\Http\Requests\Admin\Products\StoreProductRequest;
use App\Http\Requests\Admin\Products\UpdateProductRequest;
use App\Services\ProductService;
use App\Services\ProductVariantService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AdminProductWriteService
{
    public function __construct(
        protected ProductService $products,
        protected ProductVariantService $variants
    ) {
    }

    public function create(StoreProductRequest $request): void
    {
        $this->assertUniqueVariantCombinations($request->input('variants', []));

        DB::beginTransaction();

        try {
            $product = $this->products->createProduct($request->all());

            if (!$product) {
                throw new RuntimeException('Không thể tạo sản phẩm.');
            }

            foreach ($request->input('variants', []) as $index => $variant) {
                $variant['id_product'] = $product->id;
                $variant['price'] = $variant['price'] ?? 0;
                $variant['quantity'] = $variant['quantity'] ?? 0;
                $variant['status'] = $variant['status'] ?? 'active';

                if ($request->hasFile("variants.$index.image")) {
                    $variant['image'] = $request->file("variants.$index.image");
                }

                if (!$this->variants->createProductVariant($variant)) {
                    throw new RuntimeException('Không thể tạo biến thể sản phẩm.');
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Lỗi khi thêm sản phẩm: ' . $e->getMessage());

            throw $e;
        }
    }

    public function update(UpdateProductRequest $request, int $id): void
    {
        if (!$this->products->updateProduct($request, $id)) {
            throw new RuntimeException('Cập nhật sản phẩm thất bại.');
        }
    }

    public function updateProductStatus(int $id, string $status): string
    {
        if (!$this->products->updateStatus($id, $status)) {
            throw new RuntimeException('Cập nhật trạng thái sản phẩm thất bại.');
        }

        return $status === 'active' ? 'Đã bật sản phẩm' : 'Đã tắt sản phẩm';
    }

    public function softDelete(int $id): void
    {
        if (!$this->products->deleteProduct($id)) {
            throw new RuntimeException('Xóa sản phẩm thất bại.');
        }
    }

    public function restore(int $id): void
    {
        if (!$this->products->restoreProduct($id)) {
            throw new RuntimeException('Khôi phục sản phẩm thất bại.');
        }
    }

    public function forceDelete(int $id): void
    {
        if (!$this->products->delete($id)) {
            throw new RuntimeException('Xóa vĩnh viễn sản phẩm thất bại.');
        }
    }

    public function bulkDelete(array $ids): void
    {
        if (!$this->products->bulkDelete($ids)) {
            throw new RuntimeException('Xóa hàng loạt thất bại.');
        }
    }

    public function bulkRestore(array $ids): void
    {
        if (!$this->products->bulkRestore($ids)) {
            throw new RuntimeException('Khôi phục hàng loạt thất bại.');
        }
    }

    protected function assertUniqueVariantCombinations(array $variants): void
    {
        $combinations = [];

        foreach ($variants as $variant) {
            $key = ($variant['id_color'] ?? '') . '-' . ($variant['id_size'] ?? '');

            if (in_array($key, $combinations, true)) {
                throw new RuntimeException('Biến thể bị trùng màu sắc và kích cỡ.');
            }

            $combinations[] = $key;
        }
    }
}
