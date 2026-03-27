<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductVariant;

class StockController extends Controller
{
    public function getStock(Request $request)
    {
        $productId = $request->input('product_id');
        $colorId   = $request->input('color_id');
        $sizeId    = $request->input('size_id');

        $variant = ProductVariant::where('id_product', $productId)
            ->where('id_color', $colorId)
            ->where('id_size', $sizeId)
            ->first();

        if (!$variant) {
            return response()->json([
                'success' => false,
                'quantity' => 0,
                'message' => 'Không tìm thấy biến thể sản phẩm'
            ]);
        }

        return response()->json([
            'success' => true,
            'quantity' => $variant->quantity
        ]);
    }
}
