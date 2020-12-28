<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;


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

Route::namespace('Api')->group(function () {

//Rutas del controlador de usuario
Route::resource('user', 'App\Http\Controllers\UserController');
Route::post('login','App\Http\Controllers\UserController@login');
//Route::put('/user/update','App\Http\Controllers\UserController@update');
//Route::post('/user/upload','App\Http\Controllers\UserController@upload')->middleware('api.auth');
//Route::get('/user/avatar/{filename}','App\Http\Controllers\UserController@getImage');

});

