<?php

use App\Http\Controllers\User\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Users Routes
Route::group(['prefix' => 'users'], function () {
    // Users Routes
    Route::get('/', [UserController::class, 'index'])
        ->middleware('role:SUPER_ADMIN')
        ->name('users.index');
    Route::post('/', [UserController::class, 'store'])
        ->middleware('role:SUPER_ADMIN')
        ->name('users.store');
    Route::post('/{model}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/{model}', [UserController::class, 'destroy'])
        ->middleware('role:SUPER_ADMIN')
        ->name('users.destroy');
    Route::post('{model}/restore', [UserController::class, 'restore'])
        ->middleware('role:SUPER_ADMIN')
        ->name('users.restore');
});
