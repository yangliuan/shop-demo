<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Goods;
use Faker\Generator as Faker;

$factory->define(Goods::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'stock' => 50
    ];
});
