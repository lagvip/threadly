<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;

class ProductController extends Controller
{
    public function show($id)
    {
        $product = Product::with([
            'variants' => function ($q) {
                $q->where('status', 'active')->with(['color', 'size']);
            },
            'category',
            'brand'
        ])
        ->available()
        ->findOrFail($id);

        $variant = $product->variants->first();

        $relatedProducts = Product::with([
            'variants' => function ($q) {
                $q->where('status', 'active')->with(['color', 'size']);
            },
            'category'
        ])
        ->available()
        ->where('id_category', $product->id_category)
        ->where('id', '!=', $product->id)
        ->take(8)
        ->get();

        $reviews = Review::with([
            'user:id,name,avatar',
            'admin:id,name,avatar'
        ])
            ->where('product_id', $product->id)
            ->whereNotNull('comment')
            ->orderByDesc('created_at')
            ->get();

        $reviewCount = $reviews->count();
        $averageRating = $reviewCount > 0
            ? round((float) $reviews->avg('rating'), 1)
            : 0;

        $ratingSummary = collect(range(5, 1))->mapWithKeys(function ($star) use ($reviews, $reviewCount) {
            $count = $reviews->where('rating', $star)->count();
            $percent = $reviewCount > 0 ? round(($count / $reviewCount) * 100) : 0;

            return [
                $star => [
                    'count' => $count,
                    'percent' => $percent,
                ],
            ];
        });

        return view('client.product_detail', compact(
            'product',
            'variant',
            'relatedProducts',
            'reviews',
            'reviewCount',
            'averageRating',
            'ratingSummary'
        ));
    }
}
