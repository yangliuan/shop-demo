<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'goods_id', 'goods_name', 'number'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];

    protected $casts = [];

    protected $appends = [];
}
