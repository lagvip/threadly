<?php

namespace App\Contracts\Repositories;

use Carbon\Carbon;

interface DashboardRepositoryInterface
{
    public function successfulOrdersQuery(Carbon $fromDay, Carbon $toDay);

    public function soldQuantity(Carbon $fromDay, Carbon $toDay): float;

    public function topProducts(Carbon $fromDay, Carbon $toDay);

    public function categoryStats(Carbon $fromDay, Carbon $toDay);

    public function revenueRows(Carbon $fromDay, Carbon $toDay);
}
