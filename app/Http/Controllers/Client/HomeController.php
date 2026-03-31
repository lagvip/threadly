<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $banners = Banner::where('is_active', 1)
            ->orderBy('position', 'asc')
            ->get();

        $categories = Category::with('parent')
            ->whereNotNull('id_parent')
            ->get();

        $activeProductsQuery = Product::with(['variants' => function ($q) {
                $q->where('status', 'active')->orderBy('price', 'asc');
            }])
            ->available()
            ->whereHas('variants', function ($q) {
                $q->where('status', 'active');
            });

        $shoppingProducts = (clone $activeProductsQuery)
            ->inRandomOrder()
            ->limit(10)
            ->get();

        $kitchenProducts = (clone $activeProductsQuery)
            ->inRandomOrder()
            ->limit(10)
            ->get();

        $trendingProducts = (clone $activeProductsQuery)
            ->inRandomOrder()
            ->limit(3)
            ->get();

        $soldProductIds = DB::table('order_details')
            ->join('products', 'products.id', '=', 'order_details.product_id')
            ->where('products.status', 'active')
            ->whereNull('products.deleted_at')
            ->select('order_details.product_id', DB::raw('SUM(order_details.quantity) as total_sold'))
            ->groupBy('order_details.product_id')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->pluck('order_details.product_id')
            ->toArray();

        $featuredProducts = collect();

        if (!empty($soldProductIds)) {
            $featuredProducts = (clone $activeProductsQuery)
                ->whereIn('id', $soldProductIds)
                ->get()
                ->sortBy(function ($product) use ($soldProductIds) {
                    return array_search($product->id, $soldProductIds);
                })
                ->values();
        }

        if ($featuredProducts->count() < 10) {
            $excludeIds = $featuredProducts->pluck('id')->all();

            $fillProducts = (clone $activeProductsQuery)
                ->when(!empty($excludeIds), function ($q) use ($excludeIds) {
                    $q->whereNotIn('id', $excludeIds);
                })
                ->inRandomOrder()
                ->limit(10 - $featuredProducts->count())
                ->get();

            $featuredProducts = $featuredProducts->concat($fillProducts);
        }

        $bestSellerProducts = $featuredProducts;

        return view('client.home', compact(
            'banners',
            'categories',
            'shoppingProducts',
            'featuredProducts',
            'kitchenProducts',
            'trendingProducts',
            'bestSellerProducts'
        ));
    }
}
