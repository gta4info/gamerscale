<?php

namespace App\Http\Controllers;

use App\Http\Enums\PrizeStatusEnum;
use App\Http\Enums\LeaderboardStatusEnum;
use App\Http\Enums\PrizeTypeEnum;
use App\Models\Leaderboard;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
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

        foreach ($userObj->prizes()->orderByDesc('id')->get() as $prize) {
            // Calculate all GSPoints to single item
            if($prize->prize->type === PrizeTypeEnum::GSPOINTS->value) {
                if($prize->status === PrizeStatusEnum::COMPLETED->value) {
                    $prizes['gspoints']['amountReceived'] = $prizes['gspoints']['amountReceived'] + $prize->prize->value;
                } else {
                    $prizes['gspoints']['amountAwaiting'] = $prizes['gspoints']['amountAwaiting'] + $prize->prize->value;
                }
            } else { // Each other prize has self item
                $prize->status = $this->resolvePrizeStatusArray($prize->status);

                $prize->cardText = $this->resolvePrizeCardText($prize);
                $prizes['others'][] = $prize;
            }
        }

        return Inertia::render('Front/Profile', [
            'user' => $userObj,
            'prizes' => collect($prizes)
        ]);
    }

    public function resolvePrizeStatusArray(int $status): array
    {
        return match ($status) {
            PrizeStatusEnum::IN_PROGRESS->value => ['value' => $status, 'text' => 'В процессе выдачи', 'class' => 'in-progress'],
            PrizeStatusEnum::COMPLETED->value => ['value' => $status, 'text' => 'Получен', 'class' => 'completed'],
            default => ['value' => $status, 'text' => 'В ожидании', 'class' => 'pending'],
        };
    }

    public function resolvePrizeCardText($prize): string
    {
        return match($prize->prizable_type) {
            'App\Models\Achievement' => "Получен за достижение <strong>{$prize->data->level} уровня</strong> в ачивке <strong>\"{$prize->parent->title}\"</strong>",
            default => 'Автоматическая выдача приза'
        };
    }

    public function achievements(): Response
    {
        $achievements = Cache::remember('user-achievements-id-'.auth()->id(), 60, function () {
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

    public function quests(): Response
    {
        $quests = [];
        return Inertia::render('Front/Quests', [
            'quests' => $quests
        ]);
    }

    public function tournaments(): Response
    {
        $tournaments = [];
        return Inertia::render('Front/Tournaments', [
            'tournaments' => $tournaments
        ]);
    }
}
