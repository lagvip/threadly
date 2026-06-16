<?php

namespace App\Providers\Modules;

use App\Contracts\Repositories\PasswordResetTokenRepositoryInterface;
use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Repositories\Eloquent\PasswordResetTokenRepository;
use App\Repositories\Eloquent\RoleRepository;
use Illuminate\Support\ServiceProvider;

class SystemRepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PasswordResetTokenRepositoryInterface::class, PasswordResetTokenRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
    }
}
