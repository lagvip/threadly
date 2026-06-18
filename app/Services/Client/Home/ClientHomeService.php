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
    ) {}

    public function indexData(): array
    {
        $featuredProducts = $this->featuredProducts();

        return [
            'banners' => $this->banners->activeOrdered(),
            'categories' => $this->categories->childCategories(),
            'shoppingProducts' => $this->products->randomActiveProducts(10),
            'featuredProducts' => $featuredProducts,
            'kitchenProducts' => $this->products->randomActiveProducts(10),
            'trendingProducts' => $this->products->randomActiveProducts(3),
            'bestSellerProducts' => $featuredProducts,
        ];
    }

    protected function featuredProducts()
    {
        $soldProductIds = $this->products->topSoldProductIds(12);

        return $this->products->featuredActiveProducts($soldProductIds, 10);
    }
}
