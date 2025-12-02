<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    //
    // Method for Testing an Api Request with Response by Postman Api
    public function test(): string
    {
        return 'test the api controller';
    }

    // The First Api on Task
    public function getProductById(int $productId): \Illuminate\Http\JsonResponse
    {
        $product = Product::findOrFail($productId);

        return response()->json($product);
    }
}
