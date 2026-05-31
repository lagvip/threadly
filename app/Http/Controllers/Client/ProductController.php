<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Products\ProductSearchRequest;
use App\Services\Client\Products\ClientProductCatalogService;

class ProductController extends Controller
{
    public function __construct(protected ClientProductCatalogService $catalog)
    {
    }

    public function show($id)
    {
        return view('client.product_detail', $this->catalog->detailData((int) $id));
    }

    public function search(ProductSearchRequest $request)
    {
        return view('client.search.index', $this->catalog->searchData($request->filters()));
    }
}
