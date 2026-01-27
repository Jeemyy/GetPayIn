<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hold;
use App\Models\Order;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    use ApiTrait;
    public function createOrder(Request $request){
        try {
            $data = $request->validate([
                'hold_id' => 'required|exists:holds,id',
            ]);

            $holdId = (int) $data['hold_id'];

            $order = DB::transaction(function () use ($holdId) {
                $hold = Hold::where('id', $holdId)
                    ->lockForUpdate()
                    ->first();

                if (! $hold) {
                    throw ValidationException::withMessages([
                        'hold_id' => ['The selected hold is invalid.'],
                    ]);
                }

                if ($hold->used) {
                    throw ValidationException::withMessages([
                        'hold_id' => ['This hold has already been used.'],
                    ]);
                }

                if ($hold->expires_at < now()) {
                    throw ValidationException::withMessages([
                        'hold_id' => ['This hold has expired.'],
                    ]);
                }

               $order = Order::create([
                    'hold_id' => $hold->id,
                    'status' => 'pending'
               ]);

               $hold->used = true;
               $hold->save();

                return $order;
            });

            $orderId = $order->id;
            $order = Order::find($orderId);
            return $this->returnData('Order ID',$orderId);
            
        } catch (ValidationException $e) {
            return $this->returnError('No Query In Here!: Something Wrong');
        } 
    }
}
