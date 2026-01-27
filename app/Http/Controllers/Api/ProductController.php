<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Hold;
use App\Traits\ApiTrait;
use Illuminate\Support\Facades\Cache;
use Exception;


class ProductController extends Controller
{
    use ApiTrait;
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
            
            
            $product = Product::find($productId);
            return $this->returnData("Product", $product);

        } catch (Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }
}
