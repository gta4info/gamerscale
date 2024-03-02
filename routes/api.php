<?php

use App\Http\Controllers\RaffleController;
use App\Http\Controllers\UserBalanceController;
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

Route::post('user/store-balances', [UserBalanceController::class, 'store']);

Route::prefix('raffles')->controller(RaffleController::class)->group(function () {
    Route::post('create', 'create');
    Route::post('update/{raffle}', 'update');
    Route::post('participate/{raffle}', 'participate');
    Route::get('user-summaries/{raffle}', 'getUserSummaries');
});
