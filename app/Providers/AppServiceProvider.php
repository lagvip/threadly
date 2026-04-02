<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap(5);

        View::composer('client.partials.header', function ($view) {
            $headerCategories = Category::query()
                ->whereNull('id_parent')
                ->with('childrenRecursive')
                ->get();

            $view->with('headerCategories', $headerCategories);
        });
    }
}
