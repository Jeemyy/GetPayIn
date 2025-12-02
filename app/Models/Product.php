<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
    public function holds(){
        return $this->hasMany(Hold::class);
    }
}
