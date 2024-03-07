<?php

namespace App\Http\Controllers;

use App\Http\Enums\UserBalanceTypeEnum;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class UserBalanceController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $user = (new UserController())->updateOrCreate($request);

        try {
            /** Check and update fiat */
            $lastFiatAmount = $this->getCurrentBalanceByType($user, UserBalanceTypeEnum::FIAT->value);
            $fiatAmount = (float)$request->post('fiat');

            if($fiatAmount != $lastFiatAmount) {
                $user->balance()->create([
                    'amount' => $fiatAmount,
                    'type' => UserBalanceTypeEnum::FIAT->value
                ]);
            }

            /** Check and update VBucks */
            $lastVbucksAmount = $this->getCurrentBalanceByType($user, UserBalanceTypeEnum::VBUCKS->value);
            $vbucksAmount = (float)$request->post('vbucks');

            if($vbucksAmount != $lastVbucksAmount) {
                $user->balance()->create([
                    'amount' => $vbucksAmount,
                    'type' => UserBalanceTypeEnum::VBUCKS->value
                ]);
            }

            return response()->json([
                'status' => 200
            ]);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            Log::error(json_encode($request->all()));
            return response()->json([
                'message' => 'Can\'t store balances for user.',
                'status' => 400
            ], 400);
        }
    }

    public function getCurrentBalanceByType(User $user, int $type): float
    {
        return (float)$user->balance()
            ->where('type', '=', $type)
            ->latest('id')
            ->value('amount') ?? 0;
    }

    public function getBalance(Request $request): JsonResponse
    {
        $user = (new UserController())->updateOrCreate($request);

        return response()->json([
            'vbucks' => $this->getCurrentBalanceByType($user, UserBalanceTypeEnum::VBUCKS->value),
            'fiat' => $this->getCurrentBalanceByType($user, UserBalanceTypeEnum::FIAT->value)
        ]);
    }

    public function create(Request $request): JsonResponse
    {
        $user = (new UserController())->updateOrCreate($request);

        $currencyType = (int)$request->post('currency_type');
        $action = $request->post('action');
        $amount = $request->post('amount');

        try {
            $currentAmount = $this->getCurrentBalanceByType($user, $currencyType);
            $newAmount = 0;

            if($action === 'give') {
                $newAmount = $currentAmount + $amount;
            } else {
                if($currentAmount - $amount > 0) {
                    $newAmount = $currentAmount - $amount;
                }
            }

            $user->balance()->create([
                'amount' => $newAmount,
                'type' => $currencyType,
                'comment' => 'Управление балансом через команду в дискорде'
            ]);

            return response()->json([
                'message' => "Баланс пользователя успешно изменен."
            ]);
        } catch (\Exception $exception) {
            Log::info('Error on create balance for user id: ' . $user);
            Log::info(json_encode($request->all()));
            Log::error($exception->getMessage());

            return response()->json([
                'message' => 'Ошибка при обновлении баланса пользователю.'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
