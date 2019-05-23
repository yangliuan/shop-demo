<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group(['prefix' => 'stock'], function () {
    Route::post('case1', 'SecKillController@caseOne');
    Route::post('case2', 'SecKillController@caseTwo');
    Route::post('case3', 'SecKillController@caseThree');
    Route::post('case4', 'SecKillController@caseFour');
    Route::post('case5', 'SecKillController@caseFive');
});
Route::apiResources([
    'goods' => 'GoodsController'
]);
