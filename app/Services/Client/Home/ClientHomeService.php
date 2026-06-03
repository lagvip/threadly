<?php

namespace App\Services\Client\Home;

use App\Contracts\Repositories\BannerRepositoryInterface;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;

class ClientHomeService
{
    public function __construct(
        protected BannerRepositoryInterface $banners,
        protected CategoryRepositoryInterface $categories,
        protected ProductRepositoryInterface $products
    ) {
    }

    public function indexData(): array
    {
        $activeProductsQuery = $this->products->activeProductsQuery();
        $featuredProducts = $this->featuredProducts($activeProductsQuery);

        return [
            'banners' => $this->banners->activeOrdered(),
            'categories' => $this->categories->childCategories(),
            'shoppingProducts' => (clone $activeProductsQuery)->inRandomOrder()->limit(10)->get(),
            'featuredProducts' => $featuredProducts,
            'kitchenProducts' => (clone $activeProductsQuery)->inRandomOrder()->limit(10)->get(),
            'trendingProducts' => (clone $activeProductsQuery)->inRandomOrder()->limit(3)->get(),
            'bestSellerProducts' => $featuredProducts,
        ];
    }

    protected function featuredProducts($activeProductsQuery)
    {
        $soldProductIds = $this->products->topSoldProductIds(12);

        $featuredProducts = collect();

        if (!empty($soldProductIds)) {
            $featuredProducts = (clone $activeProductsQuery)
                ->whereIn('id', $soldProductIds)
                ->get()
                ->sortBy(fn ($product) => array_search($product->id, $soldProductIds))
                ->values();
        }

        if ($featuredProducts->count() >= 10) {
            return $featuredProducts;
        }

        $excludeIds = $featuredProducts->pluck('id')->all();
        $fillProducts = (clone $activeProductsQuery)
            ->when(!empty($excludeIds), fn ($q) => $q->whereNotIn('id', $excludeIds))
            ->inRandomOrder()
            ->limit(10 - $featuredProducts->count())
            ->get();

        return $featuredProducts->concat($fillProducts);
    }
}
