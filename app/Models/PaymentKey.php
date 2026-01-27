<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentKey extends Model
{
    //
    protected $table = 'payment_keys';
    // protected $fillable = ['id']; 
    protected $fillable = [
        'idempotency',
        'order_id',
        'processed_at',
    ];
    protected $guarded = ['id'];
    public function order(){
        return $this->belongsTo(Order::class);
    }
}
