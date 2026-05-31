<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Product;
use App\Models\RefundRequest;
use App\Models\Review;
use App\Models\Voucher;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use App\Policies\RefundRequestPolicy;
use App\Policies\ReviewPolicy;
use App\Policies\VoucherPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Order::class => OrderPolicy::class,
        Product::class => ProductPolicy::class,
        RefundRequest::class => RefundRequestPolicy::class,
        Voucher::class => VoucherPolicy::class,
        Review::class => ReviewPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
