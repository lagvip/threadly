<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use Illuminate\Http\Request;

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

    public function search(Request $request)
    {
        $keyword = trim((string) $request->input('q', ''));
        $sort = (string) $request->input('sort', 'newest');

        $productsQuery = Product::query()
            ->available()
            ->with([
                'brand',
                'category',
                'reviews',
                'variants' => function ($q) {
                    $q->where('status', 'active')
                        ->with(['color', 'size'])
                        ->orderBy('price', 'asc');
                },
            ]);

        if ($keyword !== '') {
            $keywordLike = $this->accentSensitiveLikePattern($keyword);

            $productsQuery->where(function ($q) use ($keywordLike) {
                $q->whereRaw('LOWER(products.name) COLLATE utf8mb4_bin LIKE ?', [$keywordLike])
                    ->orWhereHas('brand', function ($brandQuery) use ($keywordLike) {
                        $brandQuery->whereRaw('LOWER(brands.name) COLLATE utf8mb4_bin LIKE ?', [$keywordLike]);
                    })
                    ->orWhereHas('category', function ($categoryQuery) use ($keywordLike) {
                        $categoryQuery->whereRaw('LOWER(categories.name) COLLATE utf8mb4_bin LIKE ?', [$keywordLike]);
                    });
            });
        }

        if ($request->filled('category_id')) {
            $category = Category::with('childrenRecursive')->find($request->input('category_id'));

            if ($category) {
                $categoryIds = $this->collectCategoryIds($category);
                $productsQuery->whereIn('id_category', $categoryIds);
            }
        }

        $brandIds = array_values(array_filter((array) $request->input('brand', [])));
        if (!empty($brandIds)) {
            $productsQuery->whereIn('id_brand', $brandIds);
        }

        $priceMin = $request->input('price_min');
        $priceMax = $request->input('price_max');

        if (is_numeric($priceMin) || is_numeric($priceMax)) {
            $min = is_numeric($priceMin) ? (float) $priceMin : 0;
            $max = is_numeric($priceMax) ? (float) $priceMax : null;

            $productsQuery->whereHas('variants', function ($q) use ($min, $max) {
                $q->where('status', 'active')
                    ->where('price', '>=', $min);

                if ($max !== null) {
                    $q->where('price', '<=', $max);
                }
            });
        }

        if ($sort === 'price_asc') {
            $productsQuery->orderByRaw("
                (
                    select min(pv.price)
                    from product_variants pv
                    where pv.id_product = products.id
                    and pv.status = 'active'
                    and pv.deleted_at is null
                ) asc
            ");
        } elseif ($sort === 'price_desc') {
            $productsQuery->orderByRaw("
                (
                    select min(pv.price)
                    from product_variants pv
                    where pv.id_product = products.id
                    and pv.status = 'active'
                    and pv.deleted_at is null
                ) desc
            ");
        } else {
            $productsQuery->orderByDesc('created_at');
        }

        $products = $productsQuery
            ->paginate(16)
            ->appends($request->query());

        $categories = Category::query()
            ->where(function ($q) {
                $q->whereNull('id_parent')
                    ->orWhere('id_parent', 0);
            })
            ->with('childrenRecursive')
            ->orderBy('name')
            ->get();

        $brands = Brand::query()
            ->orderBy('name')
            ->get();

        $variantsQuery = ProductVariant::query()
            ->where('status', 'active')
            ->whereHas('product', function ($q) {
                $q->available();
            });

        $priceRangeMin = (clone $variantsQuery)->min('price');
        $priceRangeMax = (clone $variantsQuery)->max('price');

        return view('client.search.index', compact(
            'products',
            'categories',
            'brands',
            'priceRangeMin',
            'priceRangeMax',
            'keyword'
        ));
    }

    private function accentSensitiveLikePattern(string $keyword): string
    {
        return '%' . addcslashes(mb_strtolower($keyword, 'UTF-8'), '\\%_') . '%';
    }

    private function collectCategoryIds(Category $category): array
    {
        $ids = [$category->id];

        foreach ($category->childrenRecursive ?? [] as $child) {
            $ids = array_merge($ids, $this->collectCategoryIds($child));
        }

        return array_values(array_unique($ids));
    }
}
