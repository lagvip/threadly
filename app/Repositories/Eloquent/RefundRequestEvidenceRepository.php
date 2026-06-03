<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\RefundRequestEvidenceRepositoryInterface;
use App\Models\RefundRequestEvidence;

class RefundRequestEvidenceRepository implements RefundRequestEvidenceRepositoryInterface
{
    public function create(array $data): RefundRequestEvidence
    {
        return RefundRequestEvidence::create($data);
    }
}
