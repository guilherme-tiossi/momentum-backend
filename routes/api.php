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


Route::middleware('auth:sanctum')->get('check-auth', function () {
    return response()->json([
        'user' => Auth::user(),
        'session' => session()->all(),
    ]);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::withoutMiddleware([Authenticate::class])->post('create-user', [App\Http\Controllers\UserController::class, 'store'])
    ->name('create-user');

Route::middleware('auth:sanctum')->apiResource('users', App\Http\Controllers\UserController::class);
Route::middleware('auth:sanctum')->apiResource('tasks', App\Http\Controllers\TaskController::class);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/users/{userToFollow}/follow', [App\Http\Controllers\FollowController::class, 'follow']);
    Route::post('/users/{userToUnfollow}/unfollow', [App\Http\Controllers\FollowController::class, 'unfollow']);
    Route::get('/users/{user}/followers', [App\Http\Controllers\FollowController::class, 'followers']);
    Route::get('/users/{user}/following', [App\Http\Controllers\FollowController::class, 'following']);
});
