<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Goods;
use App\Orders;
use Illuminate\Support\Facades\Redis;

class SecKill implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 120;

    protected $good;

    protected $request;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Goods $good, array $request)
    {
        $this->good = $good;
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $result = DB::update(
            'update goods set stock = stock - ? where id = ? and stock >= ?',
            [$this->request['number'], $this->request['goods_id'], $this->request['number']]
        );
        if (!$result) {
            return;
        }
        Orders::create([
            'goods_id' => $this->good->id,
            'goods_name' => $this->good->name,
            'number' => $this->request['number']
        ]);
        Redis::decr('goods_id_' . $this->good->id);
    }

    public function tags()
    {
        return ['render', 'good:' . $this->good->id];
    }
}
