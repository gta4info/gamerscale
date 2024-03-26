<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\UserBalanceController;
use App\Http\Enums\ActionLogTypeEnum;
use App\Http\Enums\UserBalanceTypeEnum;
use App\Models\Achievement;
use App\Models\ActionLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class UserController extends BaseController
{
    public function view(User $user): Response
    {
        $balanceTypes = [];

        foreach (UserBalanceTypeEnum::cases() as $case) {
            $balanceTypes[] = [
                'name' => $case->name,
                'value' => $case->value,
            ];
        }

        return Inertia::render('Admin/User/View', [
            'user' => $user->load([
                'balance',
                'achievements' => function($query) {
                    $query->with('achievement')->orderBy('achievement_id');
                },
            ]),
            'current_balances' => $user->currentBalances(),
            'balance_types' => $balanceTypes
        ]);
    }

    public function setAdmin(User $user): JsonResponse
    {
        try {
            $user->update(['is_admin' => true]);
        } catch (\Exception $exception) {
            Log::error('Ошибка при установки роли админа юзеру id: '.$user->id);
            Log::error(json_encode($exception));
            return response()->json([
                'message' => 'Ошибка при выдачи роли админа пользователю.'
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message' => 'Пользователю выдана роль админа.'
        ]);
    }

    public function unsetAdmin(User $user): JsonResponse
    {
        try {
            $user->update(['is_admin' => false]);
        } catch (\Exception $exception) {
            Log::error('Ошибка при удалении роли админа юзеру id: '.$user->id);
            Log::error(json_encode($exception));
            return response()->json([
                'message' => 'Ошибка при удалении роли админа пользователю.'
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message' => 'Пользователю удалена роль админа.'
        ]);
    }

    public function updateAchievementsStatsToUser(User $user, Request $request): JsonResponse
    {
        try {
            DB::transaction(function () use($user, $request) {
                foreach ($request->post('achievements') as $item) {
                    $achievement = Achievement::find($item['achievement_id']);

                    for($i = 1; $i <= $item['level']; $i++) {
                        foreach ($achievement->prizes()->where('level', '=', $i)->get() as $prize) {
                            if(
                                $achievement->prizeUsers()
                                    ->where([
                                        'user_id' => $user->id,
                                        'prize_id' => $prize->prize_id
                                    ])
                                    ->whereJsonContains('data->level', $i)
                                    ->count() === 0
                            ) {
                                $achievement->prizeUsers()->create([
                                    'user_id' => $user->id,
                                    'prize_id' => $prize->prize_id,
                                    'data' => ['level' => $i]
                                ]);
                            }
                        }
                    }

                    $user->achievements()
                        ->where('achievement_id', '=', $item['achievement_id'])
                        ->first()
                        ->update([
                            'level' => $item['level'],
                            'progress' => $item['progress']
                        ]);
                }
            });
        } catch (\Exception $exception) {
            Log::error('Ошибка при обновлении статистики ачивок пользователю id: '.$user->id);
            Log::debug($exception->getMessage());
            Log::debug(json_encode($request->all()));

            return response()->json([
                'message' => 'Ошибка при обновлении статистики ачивок'
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message' => 'Статистика ачивок успешно обновлена.'
        ]);
    }

    public function balance(User $user, Request $request): JsonResponse
    {
        $type = $request->post('currency_type');
        $amount = $request->post('amount');

        $currency = match(true) {
            UserBalanceTypeEnum::FIAT->value === $type => 'фиата',
            UserBalanceTypeEnum::VBUCKS->value === $type => 'в-баксы',
            UserBalanceTypeEnum::GSPOINTS->value === $type => 'GS поинтов',
            default => $request->post('currency_type'),
        };

        if($request->post('action') === 'give') {
            $actionMessage = "Начислено $amount $currency";
        } else {
            $actionMessage = "Списано $amount $currency";
        }

        ActionLog::create([
            'user_id' => $request->user()->id,
            'target_id' => $user->id,
            'type' => ActionLogTypeEnum::BALANCE_CHANGE->value,
            'message' => $actionMessage
        ]);

        $result = (new UserBalanceController())->create($request, $user, 'Изменение баланса админом id: ' . $request->user()->id);

        return response()->json([
            'message' => $result->getData()->message,
            'current_balances' => $user->currentBalances()
        ], $result->status());
    }
}
