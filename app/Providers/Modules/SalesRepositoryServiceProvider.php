<?php

namespace App\Providers\Modules;

use App\Contracts\Repositories\CartRepositoryInterface;
use App\Contracts\Repositories\OrderDetailRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\OrderStatusLogRepositoryInterface;
use App\Contracts\Repositories\RefundRequestEvidenceRepositoryInterface;
use App\Contracts\Repositories\RefundRequestItemRepositoryInterface;
use App\Contracts\Repositories\RefundRequestRepositoryInterface;
use App\Contracts\Repositories\VoucherRepositoryInterface;
use App\Contracts\Repositories\WalletRepositoryInterface;
use App\Contracts\Repositories\WalletTransactionRepositoryInterface;
use App\Repositories\Eloquent\CartRepository;
use App\Repositories\Eloquent\OrderDetailRepository;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\OrderStatusLogRepository;
use App\Repositories\Eloquent\RefundRequestEvidenceRepository;
use App\Repositories\Eloquent\RefundRequestItemRepository;
use App\Repositories\Eloquent\RefundRequestRepository;
use App\Repositories\Eloquent\VoucherRepository;
use App\Repositories\Eloquent\WalletRepository;
use App\Repositories\Eloquent\WalletTransactionRepository;
use Illuminate\Support\ServiceProvider;

class SalesRepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CartRepositoryInterface::class, CartRepository::class);
        $this->app->bind(OrderDetailRepositoryInterface::class, OrderDetailRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(OrderStatusLogRepositoryInterface::class, OrderStatusLogRepository::class);
        $this->app->bind(RefundRequestEvidenceRepositoryInterface::class, RefundRequestEvidenceRepository::class);
        $this->app->bind(RefundRequestItemRepositoryInterface::class, RefundRequestItemRepository::class);
        $this->app->bind(RefundRequestRepositoryInterface::class, RefundRequestRepository::class);
        $this->app->bind(VoucherRepositoryInterface::class, VoucherRepository::class);
        $this->app->bind(WalletRepositoryInterface::class, WalletRepository::class);
        $this->app->bind(WalletTransactionRepositoryInterface::class, WalletTransactionRepository::class);
    }
}
