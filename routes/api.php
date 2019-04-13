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

Route::any('', 'Api\ClientController@index');
Route::group(['prefix' => 'v1', 'namespace' => 'Api\V1'], function () {
    //百度指数
    Route::group(['prefix'=>'baidu', 'namespace' => 'Baidu'], function(){
    	//获取指数
    	Route::get('getIndex', 'BaiduController@getIndex')->middleware('throttle:5000');
    });
});