<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Products\ProductSearchRequest;
use App\Services\Client\Products\ClientProductCatalogService;

class CategoryController extends Controller
{
    public function __construct(protected ClientProductCatalogService $catalog)
    {
    }

    public function show(ProductSearchRequest $request, $id)
    {
        return view('client.category.index', $this->catalog->categoryData((int) $id, $request->filters()));
    }
}
