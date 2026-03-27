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

        $categories = Category::whereNull('id_parent')
            ->orderBy('id', 'desc')
            ->get();

        $soldProductIds = DB::table('order_details')
            ->select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->pluck('product_id')
            ->toArray();

        $featuredProducts = Product::with(['variants' => function ($q) {
                $q->where('status', 'active');
            }])
            ->where('status', 'active')
            ->whereIn('id', $soldProductIds)
            ->get()
            ->sortBy(function ($product) use ($soldProductIds) {
                return array_search($product->id, $soldProductIds);
            });

        return view('client.home', compact('banners', 'categories', 'featuredProducts'));
    }
}
