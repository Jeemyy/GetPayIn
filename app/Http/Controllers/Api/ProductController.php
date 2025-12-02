<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Hold;
use Illuminate\Support\Facades\Cache;
use Exception;


class ProductController extends Controller
{
    //
    public function getProductById($productId)
    {
        try {
            $product = Product::findOrFail($productId);
            $allHolds = Cache::remember(
                "hold_sum_$productId",
                3,
                function () use ($productId) {
                    return Hold::where('product_id', $productId)
                    ->where('expires_at', '>', now())
                    ->sum('qty');
                }
            );
            $availableStock = $product->stock - $allHolds;
            return response()->json([
                'id' => $productId,
                'name' => $product->name,
                'price' => $product->price,
                'stock' => max(0, $availableStock),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'msg' => $e->getMessage(),
            ], 500);
        }
    }
}
