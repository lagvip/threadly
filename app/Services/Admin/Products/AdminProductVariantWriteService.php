<?php

namespace App\Services\Admin\Products;

use App\Services\ProductVariantService;
use RuntimeException;

class AdminProductVariantWriteService
{
    public function __construct(protected ProductVariantService $variants)
    {
    }

    public function updateStatus(int $id, string $status): string
    {
        if (!$this->variants->updateStatus($id, $status)) {
            $message = $status === 'active'
                ? 'Không thể bật biến thể khi sản phẩm cha đang không hoạt động'
                : 'Cập nhật trạng thái biến thể thất bại';

            throw new RuntimeException($message);
        }

        return $status === 'active' ? 'Đã bật biến thể' : 'Đã tắt biến thể';
    }

    public function bulkRestore(array $ids): void
    {
        if (!$this->variants->bulkRestore($ids)) {
            throw new RuntimeException('Khôi phục biến thể thất bại.');
        }
    }

    public function bulkForceDelete(array $ids): array
    {
        $success = [];
        $failed = [];

        foreach ($ids as $id) {
            if ($this->variants->forceDeleteProductVariant($id)) {
                $success[] = $id;
            } else {
                $failed[] = $id;
            }
        }

        if (!empty($success) && empty($failed)) {
            return [
                'success' => 'Đã xóa vĩnh viễn các biến thể đã chọn',
            ];
        }

        if (!empty($success) && !empty($failed)) {
            return [
                'success' => 'Một số biến thể đã được xóa vĩnh viễn',
                'error' => 'Một số biến thể không thể xóa do đã có trong đơn hàng',
            ];
        }

        return [
            'error' => 'Không thể xóa vĩnh viễn biến thể nào (đã có trong đơn hàng)',
        ];
    }
}
