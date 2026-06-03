<?php

namespace App\Providers;

use App\Contracts\Repositories\AddressRepositoryInterface;
use App\Contracts\Repositories\BannerRepositoryInterface;
use App\Contracts\Repositories\BrandRepositoryInterface;
use App\Contracts\Repositories\CartRepositoryInterface;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\ChatRepositoryInterface;
use App\Contracts\Repositories\ColorRepositoryInterface;
use App\Contracts\Repositories\ContactRepositoryInterface;
use App\Contracts\Repositories\DashboardRepositoryInterface;
use App\Contracts\Repositories\GhnWebhookLogRepositoryInterface;
use App\Contracts\Repositories\OrderDetailRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\OrderStatusLogRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\ProductVariantRepositoryInterface;
use App\Contracts\Repositories\RefundRequestEvidenceRepositoryInterface;
use App\Contracts\Repositories\RefundRequestItemRepositoryInterface;
use App\Contracts\Repositories\RefundRequestRepositoryInterface;
use App\Contracts\Repositories\ReviewRepositoryInterface;
use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Contracts\Repositories\SizeRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Repositories\VoucherRepositoryInterface;
use App\Contracts\Repositories\WishlistRepositoryInterface;
use App\Contracts\Repositories\WalletRepositoryInterface;
use App\Contracts\Repositories\WalletTransactionRepositoryInterface;
use App\Repositories\Eloquent\AddressRepository;
use App\Repositories\Eloquent\BannerRepository;
use App\Repositories\Eloquent\BrandRepository;
use App\Repositories\Eloquent\CartRepository;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Eloquent\ChatRepository;
use App\Repositories\Eloquent\ColorRepository;
use App\Repositories\Eloquent\ContactRepository;
use App\Repositories\Eloquent\DashboardRepository;
use App\Repositories\Eloquent\GhnWebhookLogRepository;
use App\Repositories\Eloquent\OrderDetailRepository;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\OrderStatusLogRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\ProductVariantRepository;
use App\Repositories\Eloquent\RefundRequestEvidenceRepository;
use App\Repositories\Eloquent\RefundRequestItemRepository;
use App\Repositories\Eloquent\RefundRequestRepository;
use App\Repositories\Eloquent\ReviewRepository;
use App\Repositories\Eloquent\RoleRepository;
use App\Repositories\Eloquent\SizeRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\VoucherRepository;
use App\Repositories\Eloquent\WishlistRepository;
use App\Repositories\Eloquent\WalletRepository;
use App\Repositories\Eloquent\WalletTransactionRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AddressRepositoryInterface::class, AddressRepository::class);
        $this->app->bind(BannerRepositoryInterface::class, BannerRepository::class);
        $this->app->bind(BrandRepositoryInterface::class, BrandRepository::class);
        $this->app->bind(CartRepositoryInterface::class, CartRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(ChatRepositoryInterface::class, ChatRepository::class);
        $this->app->bind(ColorRepositoryInterface::class, ColorRepository::class);
        $this->app->bind(ContactRepositoryInterface::class, ContactRepository::class);
        $this->app->bind(DashboardRepositoryInterface::class, DashboardRepository::class);
        $this->app->bind(GhnWebhookLogRepositoryInterface::class, GhnWebhookLogRepository::class);
        $this->app->bind(OrderDetailRepositoryInterface::class, OrderDetailRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(OrderStatusLogRepositoryInterface::class, OrderStatusLogRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(ProductVariantRepositoryInterface::class, ProductVariantRepository::class);
        $this->app->bind(RefundRequestEvidenceRepositoryInterface::class, RefundRequestEvidenceRepository::class);
        $this->app->bind(RefundRequestItemRepositoryInterface::class, RefundRequestItemRepository::class);
        $this->app->bind(RefundRequestRepositoryInterface::class, RefundRequestRepository::class);
        $this->app->bind(ReviewRepositoryInterface::class, ReviewRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(SizeRepositoryInterface::class, SizeRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(VoucherRepositoryInterface::class, VoucherRepository::class);
        $this->app->bind(WishlistRepositoryInterface::class, WishlistRepository::class);
        $this->app->bind(WalletRepositoryInterface::class, WalletRepository::class);
        $this->app->bind(WalletTransactionRepositoryInterface::class, WalletTransactionRepository::class);
    }
}
