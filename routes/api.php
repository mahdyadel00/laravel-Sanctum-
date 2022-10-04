<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;

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
    Route::post('/auth/register', [AuthController::class, 'createUser']);
    Route::post('/auth/login', [AuthController::class, 'loginUser']);

    Route::post('reset/send_code', 'AdminController@checkEmail');
    Route::post('reset/check_code', 'AdminController@checkCode');
    Route::post('reset/reset_password', 'AdminController@resetPassword');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::group(['middleware' => 'auth:api'], function () {
    Route::group(['middleware' => ['auth:sanctum']], function () {

    Route::get('profile', [UserController::class , 'getProfile']);
    Route::post('profile/update', [UserController::class , 'updateProfile']);
    Route::post('logout', [UserController::class , 'logout']);

});
