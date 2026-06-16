<?php

namespace App\Providers;

use App\Providers\Modules\CatalogRepositoryServiceProvider;
use App\Providers\Modules\ContentRepositoryServiceProvider;
use App\Providers\Modules\CustomerRepositoryServiceProvider;
use App\Providers\Modules\IntegrationRepositoryServiceProvider;
use App\Providers\Modules\InventoryRepositoryServiceProvider;
use App\Providers\Modules\SalesRepositoryServiceProvider;
use App\Providers\Modules\SystemRepositoryServiceProvider;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        foreach ($this->moduleProviders() as $provider) {
            $this->app->register($provider);
        }
    }

    /**
     * @return array<class-string<ServiceProvider>>
     */
    private function moduleProviders(): array
    {
        return [
            CatalogRepositoryServiceProvider::class,
            ContentRepositoryServiceProvider::class,
            CustomerRepositoryServiceProvider::class,
            IntegrationRepositoryServiceProvider::class,
            InventoryRepositoryServiceProvider::class,
            SalesRepositoryServiceProvider::class,
            SystemRepositoryServiceProvider::class,
        ];
    }
}
