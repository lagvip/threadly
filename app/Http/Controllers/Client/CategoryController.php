<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    //
    public function show(Request $request, $id)
    {
        $category = Category::with('childrenRecursive')->findOrFail($id);

        $activeCategoryIds = $this->collectActiveCategoryPathIds($category);

        $categories = Category::query()
            ->where(function ($q) {
                $q->whereNull('id_parent')->orWhere('id_parent', 0);
            })
            ->with('childrenRecursive')
            ->orderBy('name')
            ->get();

        $categoryIds = $this->collectCategoryIds($category);

        $productsQuery = Product::query()
            ->available()
            ->whereIn('id_category', $categoryIds)
            ->with([
                'brand',
                'variants' => function ($q) {
                    $q->where('status', 'active')->with(['color', 'size']);
                },
            ]);

        if ($request->filled('q')) {
            $keyword = trim((string) $request->input('q'));
            $productsQuery->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('description', 'like', '%' . $keyword . '%');
            });
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
                $q->where('status', 'active')->where('price', '>=', $min);
                if ($max !== null) {
                    $q->where('price', '<=', $max);
                }
            });
        }

        $sort = (string) $request->input('sort', 'newest');
        if ($sort === 'price_asc') {
            $productsQuery->orderByRaw("(select min(pv.price) from product_variants pv where pv.id_product = products.id and pv.status = 'active' and pv.deleted_at is null) asc");
        } elseif ($sort === 'price_desc') {
            $productsQuery->orderByRaw("(select min(pv.price) from product_variants pv where pv.id_product = products.id and pv.status = 'active' and pv.deleted_at is null) desc");
        } else {
            $productsQuery->orderByDesc('created_at');
        }

        $products = $productsQuery
            ->paginate(16)
            ->appends($request->query());

        $brands = Brand::query()
            ->orderBy('name')
            ->get();

        $variantsQuery = ProductVariant::query()
            ->where('status', 'active')
            ->whereHas('product', function ($q) use ($categoryIds) {
                $q->available()->whereIn('id_category', $categoryIds);
            });

        $priceRangeMin = (clone $variantsQuery)->min('price');
        $priceRangeMax = (clone $variantsQuery)->max('price');

        return view('client.category.index', compact(
            'category',
            'products',
            'categories',
            'activeCategoryIds',
            'brands',
            'priceRangeMin',
            'priceRangeMax'
        ));
    }

    private function collectCategoryIds(Category $category): array
    {
        $ids = [$category->id];

        foreach ($category->childrenRecursive ?? [] as $child) {
            $ids = array_merge($ids, $this->collectCategoryIds($child));
        }

        return array_values(array_unique($ids));
    }

    private function collectActiveCategoryPathIds(Category $category): array
    {
        $ids = [];
        $current = $category;

        while ($current) {
            $ids[] = $current->id;

            if (empty($current->id_parent) || (int) $current->id_parent === 0) {
                break;
            }

            $current = Category::find($current->id_parent);
        }

        return $ids;
    }
}
