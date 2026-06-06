<?php

namespace App\Services\Admin\Refunds;

use App\Contracts\Repositories\RefundRequestRepositoryInterface;
use App\Models\RefundRequest;
use App\Support\Pagination;

class AdminRefundQueryService
{
    public function __construct(protected RefundRequestRepositoryInterface $refundRequests)
    {
    }

    public function paginated(array $filters): array
    {
        $query = $this->refundRequests->adminIndexQuery()->latest('id');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['keyword'])) {
            $keyword = trim((string) $filters['keyword']);

            $query->where(function ($q) use ($keyword) {
                $q->whereHas('order', function ($orderQuery) use ($keyword) {
                    $orderQuery->where('order_code', 'like', '%' . $keyword . '%');
                })->orWhereHas('user', function ($userQuery) use ($keyword) {
                    $userQuery->where('email', 'like', '%' . $keyword . '%')
                        ->orWhere('name', 'like', '%' . $keyword . '%');
                });
            });
        }

        return [
            'refundRequests' => Pagination::withQueryString($query->paginate(10)),
            'counts' => $this->counts(),
        ];
    }

    public function loadForShow(RefundRequest $refundRequest): RefundRequest
    {
        return $this->refundRequests->loadForShow($refundRequest);
    }

    protected function counts(): array
    {
        return [
            'pending' => $this->refundRequests->countByStatus(RefundRequest::STATUS_PENDING),
            'approved' => $this->refundRequests->countByStatus(RefundRequest::STATUS_APPROVED),
            'rejected' => $this->refundRequests->countByStatus(RefundRequest::STATUS_REJECTED),
        ];
    }
}
