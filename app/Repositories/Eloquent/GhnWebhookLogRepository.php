<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\GhnWebhookLogRepositoryInterface;
use App\Models\GhnWebhookLog;

class GhnWebhookLogRepository implements GhnWebhookLogRepositoryInterface
{
    public function create(array $data): GhnWebhookLog
    {
        return GhnWebhookLog::create($data);
    }
}
