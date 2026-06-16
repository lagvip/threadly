<?php

namespace App\Contracts\Repositories;

use App\Models\GhnWebhookLog;

interface GhnWebhookLogRepositoryInterface
{
    public function create(array $data): GhnWebhookLog;

    public function update(GhnWebhookLog $log, array $data): bool;
}
