<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //
    protected $fillable = [
        'hold_id',
        'status'
    ];
    public function holds(){
        return $this->belongsTo(Hold::class);
    }
    public function paymentkey(){
        return $this->hasOne(PaymentKey::class);
    }
    
}
