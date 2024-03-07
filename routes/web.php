<?php

use App\Http\Controllers\DiscordController;
use App\Http\Controllers\DiscordInteractionController;
use App\Http\Controllers\RaffleController;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Raffle;
use Illuminate\Support\Facades\Route;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('home');
});

/** Discord auth routes */
Route::get('auth/discord/callback', [DiscordController::class, 'handleProviderCallback']);
Route::get('auth/discord', [DiscordController::class, 'redirectToProvider']);

/** Raffles */
Route::get('raffle-create-view', function () {
    return view('raffle-create');
});
Route::get('raffle-update-view/{raffle}', function (Raffle $raffle) {
    return view('raffle-update', ['raffle' => $raffle]);
});

/** Logs viewer route */
Route::get('000-logs', [LogViewerController::class, 'index']);
