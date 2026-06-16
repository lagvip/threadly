<?php

namespace App\Providers\Modules;

use App\Contracts\Repositories\BannerRepositoryInterface;
use App\Contracts\Repositories\ChatRepositoryInterface;
use App\Contracts\Repositories\ContactRepositoryInterface;
use App\Contracts\Repositories\DashboardRepositoryInterface;
use App\Repositories\Eloquent\BannerRepository;
use App\Repositories\Eloquent\ChatRepository;
use App\Repositories\Eloquent\ContactRepository;
use App\Repositories\Eloquent\DashboardRepository;
use Illuminate\Support\ServiceProvider;

class ContentRepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BannerRepositoryInterface::class, BannerRepository::class);
        $this->app->bind(ChatRepositoryInterface::class, ChatRepository::class);
        $this->app->bind(ContactRepositoryInterface::class, ContactRepository::class);
        $this->app->bind(DashboardRepositoryInterface::class, DashboardRepository::class);
    }
}
