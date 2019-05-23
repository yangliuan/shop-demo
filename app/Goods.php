<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Goods extends Model
{
    protected $table = 'goods';

    protected $fillable = [
        'name', 'stock',
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];

    protected $casts = [];

    protected $appends = [];

    public function decreaseStock($amount)
    {
        if ($amount < 0) {

            return false;
        }

        return $this->newQuery()->where('id', $this->id)->where('stock', '>=', $amount)->decrement('stock', $amount);
    }
}
