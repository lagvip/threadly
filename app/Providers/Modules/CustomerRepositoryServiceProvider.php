<?php

namespace App\Providers\Modules;

use App\Contracts\Repositories\AddressRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Repositories\WishlistRepositoryInterface;
use App\Repositories\Eloquent\AddressRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\WishlistRepository;
use Illuminate\Support\ServiceProvider;

class CustomerRepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AddressRepositoryInterface::class, AddressRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(WishlistRepositoryInterface::class, WishlistRepository::class);
    }
}
