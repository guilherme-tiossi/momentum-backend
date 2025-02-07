<?php

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->apiResource('users', App\Http\Controllers\UserController::class);
Route::middleware('auth:sanctum')->apiResource('tasks', App\Http\Controllers\TaskController::class);

Route::withoutMiddleware([Authenticate::class])->post('create-user', [App\Http\Controllers\UserController::class, 'store'])
    ->name('create-user');

Route::middleware('auth:sanctum')->get('check-auth', function () {
        return response()->json([
            'user' => Auth::user(),
            'session' => session()->all(),
        ]);
    });
    