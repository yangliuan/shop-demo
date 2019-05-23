<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Goods;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Redis;

class GoodsController extends Controller
{
    public function store(Request $request)
    {
        $results = factory(Goods::class, 1)->create();

        return response()->json(compact('results'));
    }

    public function update(Goods $good)
    {
        if ($good->stock < 1) {
            return response()->json(['库存不足']);
        }
        $result = Redis::setex('goods_id_' . $good->id, 3600, $good->stock);

        return response()->json(compact('result'));
    }
}
