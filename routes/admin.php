<?php

use App\Http\Controllers\Admin\AchievementController;
use App\Http\Controllers\Admin\LeaderboardController;
use App\Http\Controllers\Admin\PrizesController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\DiscordController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::domain('admin.'.config('app.url'))->group(function () {
    Route::get('auth/discord/callback', [DiscordController::class, 'handleProviderCallback'])->name('admin.auth.discord.callback');
    Route::get('auth/discord', [DiscordController::class, 'redirectToProvider'])->name('admin.auth.discord');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/', function () {
            return Inertia::render('Admin/Home');
        })->name('admin.home');

        Route::prefix('users')->group(function () {
            Route::get('list', [UsersController::class, 'list']);
            Route::get('search-by-name/{query}', [UsersController::class, 'searchByName']);
        });

        Route::prefix('user')->group(function () {
            Route::get('view/{user}', [UserController::class, 'view']);
            Route::put('set-admin/{user}', [UserController::class, 'setAdmin']);
            Route::put('unset-admin/{user}', [UserController::class, 'unsetAdmin']);
            Route::post('achievements-stats-update/{user}', [UserController::class, 'updateAchievementsStatsToUser']);
            Route::post('balance/{user}', [UserController::class, 'balance']);
        });

        Route::prefix('prizes')->group(function () {
            Route::get('list', [PrizesController::class, 'list']);
            Route::get('create', [PrizesController::class, 'renderCreateView']);
            Route::post('create', [PrizesController::class, 'create']);
        });

        Route::prefix('prize')->group(function () {
            Route::get('view/{prize}', [PrizesController::class, 'view']);
            Route::post('update/{prize}', [PrizesController::class, 'update']);
            Route::post('delete/{prize}', [PrizesController::class, 'delete']);
        });

        Route::prefix('achievements')->group(function () {
            Route::get('list', [AchievementController::class, 'list']);
            Route::get('create', [AchievementController::class, 'renderCreateView']);
            Route::post('create', [AchievementController::class, 'create']);
        });

        Route::prefix('achievement')->group(function () {
            Route::get('view/{achievement}', [AchievementController::class, 'view']);
            Route::post('update/{achievement}', [AchievementController::class, 'update']);
            Route::post('delete/{achievement}', [AchievementController::class, 'delete']);
            Route::post('update-levels/{achievement}', [AchievementController::class, 'updateLevels']);
        });

        Route::prefix('leaderboards')->group(function () {
            Route::get('list', [LeaderboardController::class, 'list']);
            Route::get('create', [LeaderboardController::class, 'renderCreateView']);
            Route::post('create', [LeaderboardController::class, 'create']);
        });

        Route::prefix('leaderboard')->group(function () {
            Route::get('view/{leaderboard}', [LeaderboardController::class, 'view']);
            Route::post('update/{leaderboard}', [LeaderboardController::class, 'update']);
            Route::post('delete/{leaderboard}', [LeaderboardController::class, 'delete']);
            Route::post('update-users/{leaderboard}', [LeaderboardController::class, 'updateUsers']);
        });
    });
});
