<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    //
    public function show(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $query = Product::with([
            'variants' => function ($q) {
                $q->where('status', 'active')->orderBy('price', 'asc');
            },
            'reviews' => function ($q) {
                $q->whereNotNull('comment')
                ->with(['user:id,name,avatar', 'admin:id,name,avatar'])
                ->orderByDesc('created_at');
            }
        ])
        ->withAvg('reviews', 'rating')
        ->available()
        ->where('id_category', $id)
        ->whereHas('variants', function ($q) {
            $q->where('status', 'active');
        });

        // ===== FILTER GIÁ =====
        if ($request->filled('min_price')) {
            $query->whereHas('variants', function ($q) use ($request) {
                $q->where('price', '>=', $request->min_price);
            });
        }

        if ($request->filled('max_price')) {
            $query->whereHas('variants', function ($q) use ($request) {
                $q->where('price', '<=', $request->max_price);
            });
        }

        // ===== FILTER RATING =====
        if ($request->filled('rating')) {
            $query->having('reviews_avg_rating', '>=', $request->rating);
        }

        $products = $query->paginate(16)->appends($request->query());

        return view('client.category.index', compact('category', 'products'));
    }
}
