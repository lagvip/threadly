<?php

namespace App\Services\Admin\Refunds;

use App\Contracts\Repositories\RefundRequestRepositoryInterface;
use App\Enums\RefundRequestStatus;
use App\Models\RefundRequest;
use App\Support\Pagination;

class AdminRefundQueryService
{
    public function __construct(protected RefundRequestRepositoryInterface $refundRequests) {}

    public function paginated(array $filters): array
    {
        return [
            'refundRequests' => Pagination::withQueryString($this->refundRequests->paginateForAdmin($filters, 10)),
            'counts' => $this->counts(),
            'statusOptions' => $this->statusOptions(),
        ];
    }

    public function showData(RefundRequest $refundRequest): array
    {
        return [
            'refundRequest' => $this->refundRequests->loadForShow($refundRequest),
            'approvedStatus' => RefundRequestStatus::Approved->value,
            'pendingStatus' => RefundRequestStatus::Pending->value,
        ];
    }

    protected function counts(): array
    {
        return [
            'pending' => $this->refundRequests->countByStatus(RefundRequestStatus::Pending->value),
            'approved' => $this->refundRequests->countByStatus(RefundRequestStatus::Approved->value),
            'rejected' => $this->refundRequests->countByStatus(RefundRequestStatus::Rejected->value),
        ];
    }

    protected function statusOptions(): array
    {
        return collect(RefundRequestStatus::cases())
            ->mapWithKeys(fn (RefundRequestStatus $status) => [$status->value => $status->label()])
            ->all();
    }
}
