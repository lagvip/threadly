<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductController extends Controller
{
    public function show($id)
    {
        $product = Product::with([
            'variants' => function ($q) {
                $q->where('status', 'active')
                  ->with(['color', 'size']);
            },
            'category',
            'brand'
        ])->where('status', 'active')->findOrFail($id);

        $variant = $product->variants->first();

        $relatedProducts = Product::with([
            'variants' => function ($q) {
                $q->where('status', 'active');
            },
            'category'
        ])
        ->where('status', 'active')
        ->where('id_category', $product->id_category)
        ->where('id', '!=', $product->id)
        ->take(8)
        ->get();

        return view('client.product_detail', compact('product', 'variant', 'relatedProducts'));
    }
}
