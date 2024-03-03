<?php

use App\Http\Controllers\DiscordController;
use App\Http\Controllers\RaffleController;
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

/** Discord webhooks */
Route::get('discord-raffle-webhook', [RaffleController::class, 'handleDiscordInteraction']);

/** Logs viewer route */
Route::get('000-logs', [LogViewerController::class, 'index']);
