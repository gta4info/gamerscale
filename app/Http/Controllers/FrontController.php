<?php

namespace App\Http\Controllers;

use App\Http\Enums\AchievementPrizeUserStatusEnum;
use App\Http\Enums\LeaderboardStatusEnum;
use App\Http\Enums\PrizeTypeEnum;
use App\Models\Leaderboard;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class FrontController extends Controller
{
    public function profile(User $user = null): Response
    {
        $userObj = $user ?? auth()->user();
        $prizes = [
            'gspoints' => [
                'amountReceived' => 0,
                'amountAwaiting' => 0,
            ],
            'others' => []
        ];

        foreach ($userObj->achievementPrizes->load(['achievement', 'prize']) as $prize) {
            if($prize->prize->type === PrizeTypeEnum::GSPOINTS->value) {
                if($prize->status === AchievementPrizeUserStatusEnum::COMPLETED->value) {
                    $prizes['gspoints']['amountReceived'] = $prizes['gspoints']['amountReceived'] + $prize->prize->value;
                } else {
                    $prizes['gspoints']['amountAwaiting'] = $prizes['gspoints']['amountAwaiting'] + $prize->prize->value;
                }
            } else {
                $prize->status = match ($prize->status) {
                    AchievementPrizeUserStatusEnum::IN_PROGRESS->value => ['value' => $prize->status, 'text' => 'В процессе выдачи', 'class' => 'in-progress'],
                    AchievementPrizeUserStatusEnum::COMPLETED->value => ['value' => $prize->status, 'text' => 'Получен', 'class' => 'completed'],
                    default => ['value' => $prize->status, 'text' => 'В ожидании', 'class' => 'pending'],
                };

                $prize->cardText = "Получен за достижение <strong>{$prize->level} уровня</strong> в ачивке <strong>\"{$prize->achievement->title}\"</strong>";
                $prizes['others'][] = $prize;
            }
        }

        return Inertia::render('Front/Profile', [
            'user' => $userObj,
            'prizes' => collect($prizes)
        ]);
    }

    public function achievements(): Response
    {
        $achievements = Cache::remember('user-achievements', 60, function () {
            return auth()->user()
                ->achievements()
                ->orderBy('achievement_id')
                ->with('achievement')
                ->get();
        });

        return Inertia::render('Front/Achievements', [
            'achievements' => $achievements
        ]);
    }

    public function leaderboard(): Response
    {
        $leaderboard = Cache::remember('active-leaderboard', 60, function () {
            return Leaderboard::with([
                'users' => function($query) {
                    $query->orderByDesc('points');
                },
                'users.user'
            ])
                ->where('status', '=', LeaderboardStatusEnum::ACTIVE->value)
                ->latest()
                ->first();
        });
        return Inertia::render('Front/Leaderboard', [
            'leaderboard' => $leaderboard
        ]);
    }
}
