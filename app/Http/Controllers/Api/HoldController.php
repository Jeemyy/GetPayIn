<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\Hold;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class HoldController extends Controller
{
    //
    public function createHold(Request $request)
    {
        try {
            $data = $request->validate([
                'product_id' => 'required|exists:products,id',
                'qty'=> 'required|integer|min:1',
            ]);

            $productId= (int) $data['product_id'];
            $quantity= (int) $data['qty'];

            $hold = DB::transaction(function () use ($productId, $quantity) {
                $product = Product::where('id', $productId)
                    ->lockForUpdate()
                    ->firstOrFail();

                Hold::where('expires_at', '<', now())
                    ->where('used', false)
                    ->delete();

                $activeHoldsQty = Hold::where('product_id', $productId)
                    ->where('expires_at', '>', now())
                    ->sum('qty');

                $availableStock = $product->stock - $activeHoldsQty;

                if ($quantity > $availableStock) {
                    throw ValidationException::withMessages([
                        'qty' => ['Not Enough Stock Available'],
                    ]);
                }
                $expiresAt = now()->addMinutes(2);
                return Hold::create([
                    'product_id' => $productId,
                    'qty'=> $quantity,
                    'expires_at' => $expiresAt,
                    'used'=> false,
                ]);
            });

            // Clear cache
            Cache::forget("hold_sum_{$productId}");

            return response()->json([
                'hold_id'    => $hold->id,
                'expires_at' => $hold->expires_at,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors(),
            ], 422);
        }
    }
}
