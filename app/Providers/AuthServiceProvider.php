<?php

namespace App\Providers;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ChatConversation;
use App\Models\Color;
use App\Models\Contact;
use App\Models\InventoryReceipt;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\RefundRequest;
use App\Models\Review;
use App\Models\Role;
use App\Models\Size;
use App\Models\User;
use App\Models\Voucher;
use App\Policies\AdminOnlyResourcePolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use App\Policies\RefundRequestPolicy;
use App\Policies\ReviewPolicy;
use App\Policies\StaffResourcePolicy;
use App\Policies\VoucherPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Banner::class => StaffResourcePolicy::class,
        Brand::class => StaffResourcePolicy::class,
        Category::class => StaffResourcePolicy::class,
        ChatConversation::class => StaffResourcePolicy::class,
        Color::class => StaffResourcePolicy::class,
        Contact::class => StaffResourcePolicy::class,
        InventoryReceipt::class => StaffResourcePolicy::class,
        Order::class => OrderPolicy::class,
        OrderDetail::class => StaffResourcePolicy::class,
        Product::class => ProductPolicy::class,
        RefundRequest::class => RefundRequestPolicy::class,
        Voucher::class => VoucherPolicy::class,
        Review::class => ReviewPolicy::class,
        Role::class => AdminOnlyResourcePolicy::class,
        Size::class => StaffResourcePolicy::class,
        User::class => AdminOnlyResourcePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('viewAdminDashboard', fn (User $user): bool => $user->isStaff());
    }
}
