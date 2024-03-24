<?php

use App\Http\Controllers\DiscordController;
use App\Models\Raffle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
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

/** Admin routes */
require_once 'admin.php';

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('test', function () {
    return Inertia::render('Test');
});

/** Discord auth routes */
Route::get('auth/discord/callback', [DiscordController::class, 'handleProviderCallback'])->name('auth.discord.callback');;
Route::get('auth/discord', [DiscordController::class, 'redirectToProvider'])->name('auth.discord');

/** Raffles */
Route::get('raffle-create-view', function () {
    return view('raffle-create');
});
Route::get('raffle-update-view/{raffle}', function (Raffle $raffle) {
    return view('raffle-update', ['raffle' => $raffle]);
});

/** Logs viewer route */
Route::get('000-logs', [LogViewerController::class, 'index']);

Route::get('logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');
