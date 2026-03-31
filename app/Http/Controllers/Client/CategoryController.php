<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    //
    public function show($id)
    {   
        $category = Category::findOrFail($id);
        
        $products = Product::with('variants')->where('id_category',$id)->paginate(16);

        return view('client.category.index', compact(
            'category',
            'products'
        ));
    }
}
