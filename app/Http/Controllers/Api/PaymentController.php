<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use App\Models\PaymentKey;
use App\Models\Order;
use App\Models\Hold;
use App\Traits\ApiTrait;

class PaymentController extends Controller
{
    use ApiTrait;
    public function createPaymentWebHook(Request $request){
        try{
            $data = $request->validate([
                'idempotency' => "required|string",
                'order_id' => 'required|exists:orders,id',
                'status' => 'required|in:success,failur',
            ]);
            $idEmpotency = $data['idempotency'];
            $orderId = (int) $data['order_id'];
            $status = $data['status'];


            $result = DB::transaction(
                function() use ($idEmpotency, $orderId, $status){
                    $paymentKey = PaymentKey::where('idempotency', $idEmpotency)
                    ->first();
                    if($paymentKey){
                        return response()->json(['msg' => "The Webhook Is Already Processed"], 422);
                    }
                    $order = Order::where('id', $orderId)
                    ->lockForUpdate()
                    ->first();

                    if(!$order){   
                        throw ValidationException::withMessages([
                            'order_id' => ['Order Not Found']
                        ]);
                    }

                    if($order->status === 'paid' || $order->status === 'cancelled'){
                        return response()->json(['msg' => "The Order Is Already Completed"], 422);
                    }

                    PaymentKey::create([
                        'idempotency' => $idEmpotency,
                        'order_id' => $orderId,
                        'processed_at' => now(),
                    ]);

                    if($status === 'success'){
                        $order->status = 'paid';
                        $order->save();
                    }else{
                        $order->status = 'cancelled';
                        $order->save();
                        
                        $hold = Hold::where('id', $order->hold_id)
                        ->lockForUpdate()
                        ->first();
                        if($hold){
                            $hold->used = false;
                            $hold->save();
                        }
                    }
                    $msg = [
                        "Payment" => "The Payment Process Is Success",
                        'Order Status' => $order->status
                    ];
                    return $this->returnSuccess($msg);
                }
            );
            return $result;
        }catch(ValidationException $e){
            return $this->returnError("Something Woring");
        }
    }
}
