<?php

namespace App\Providers\Modules;

use App\Contracts\Repositories\GhnWebhookLogRepositoryInterface;
use App\Repositories\Eloquent\GhnWebhookLogRepository;
use Illuminate\Support\ServiceProvider;

class IntegrationRepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(GhnWebhookLogRepositoryInterface::class, GhnWebhookLogRepository::class);
    }
}
