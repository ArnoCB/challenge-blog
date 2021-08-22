<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::group(['middleware' => ['jwt.verify']], function() {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResources([
        'blogs' => BlogController::class
    ]);
});

Route::fallback(function(){
    return response()->json([
        'error' => 'Page Not Found.'], 404);
});
