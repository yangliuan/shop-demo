<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Goods;
use Illuminate\Validation\Rule;
use App\Orders;
use Illuminate\Support\Facades\Redis;
use App\Jobs\SecKill;

class SecKillController extends Controller
{
    //乐观锁
    public function caseOne(Request $request)
    {
        $this->validate($request, [
            'number' => 'bail|required|integer|min:1',
            'goods_id' => [
                'bail', 'required', 'integer',
                Rule::exists('goods', 'id')->where(function ($query) use ($request) {
                    $query->where('stock', '>=', $request->number);
                }),
            ]
        ]);
        DB::transaction(function () use ($request) {
            $goods = Goods::find($request->goods_id);
            $result = DB::update('update goods set stock = stock - ? where id = ? and stock >= ?', [$request->number, $request->goods_id, $request->number]);
            if (!$result) {
                return response()->json(['message' => '失败']);
            }
            Orders::create([
                'goods_id' => $goods->id,
                'goods_name' => $goods->name,
                'number' => $request->number,
            ]);
        });

        return response()->json();
    }

    //悲观锁
    public function caseTwo(Request $request)
    {
        $this->validate($request, [
            'number' => 'bail|required|integer|min:1',
            'goods_id' => [
                'bail', 'required', 'integer',
                Rule::exists('goods', 'id')->where(function ($query) use ($request) {
                    $query->where('stock', '>=', $request->number);
                }),
            ]
        ]);
        DB::transaction(function () use ($request) {
            $goods = Goods::lockForUpdate()->find($request->goods_id);
            $result = DB::update('update goods set stock = stock - ? where id = ? and stock >= ?', [$request->number, $request->goods_id, $request->number]);
            if (!$result) {
                return response()->json(['message' => '失败']);
            }
            Orders::create([
                'goods_id' => $goods->id,
                'goods_name' => $goods->name,
                'number' => $request->number,
            ]);
        });

        return response()->json();
    }

    //不用事务
    public function caseThree(Request $request)
    {
        $this->validate($request, [
            'number' => 'bail|required|integer|min:1',
            'goods_id' => [
                'bail', 'required', 'integer',
                Rule::exists('goods', 'id')->where(function ($query) use ($request) {
                    $query->where('stock', '>=', $request->number);
                }),
            ]
        ]);
        $goods = Goods::find($request->goods_id);
        $result = DB::update('update goods set stock = stock - ? where id = ? and stock >= ?', [$request->number, $request->goods_id, $request->number]);
        if (!$result) {
            return response()->json(['message' => '失败']);
        }
        Orders::create([
            'goods_id' => $goods->id,
            'goods_name' => $goods->name,
            'number' => $request->number,
        ]);
    }

    //redis库存
    public function caseFour(Request $request)
    {
        $this->validate($request, [
            'number' => 'bail|required|integer|min:1',
            'goods_id' => [
                'bail', 'required', 'integer',
                function ($attributes, $value, $fail) use ($request) {
                    $stock = Redis::get('goods_id_' . $value);
                    if (is_null($stock)) {
                        return $fail('商品不存在');
                    }
                    if ($stock < $request->number) {
                        return $fail('库存不足');
                    }
                }
            ]
        ]);
        $goods = Goods::find($request->goods_id);
        $result = DB::update('update goods set stock = stock - ? where id = ? and stock >= ?', [$request->number, $request->goods_id, $request->number]);
        if (!$result) {
            return response()->json(['message' => '失败']);
        }
        Orders::create([
            'goods_id' => $goods->id,
            'goods_name' => $goods->name,
            'number' => $request->number,
        ]);
        Redis::decr('goods_id_' . $request->goods_id);

        return response()->json();
    }

    public function caseFive(Request $request)
    {
        $this->validate($request, [
            'number' => 'bail|required|integer|min:1',
            'goods_id' => [
                'bail', 'required', 'integer',
                function ($attributes, $value, $fail) use ($request) {
                    $stock = Redis::get('goods_id_' . $value);
                    if (is_null($stock)) {
                        return $fail('商品不存在');
                    }
                    if ($stock < $request->number) {
                        return $fail('库存不足');
                    }
                }
            ]
        ]);
        $goods = Goods::find($request->goods_id);
        SecKill::dispatch($goods, $request->all());

        return response()->json();
    }
}
