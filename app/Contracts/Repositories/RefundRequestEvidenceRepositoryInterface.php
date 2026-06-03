<?php

namespace App\Contracts\Repositories;

use App\Models\RefundRequestEvidence;

interface RefundRequestEvidenceRepositoryInterface
{
    public function create(array $data): RefundRequestEvidence;
}
