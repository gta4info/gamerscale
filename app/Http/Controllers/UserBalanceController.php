<?php

namespace App\Http\Controllers;

use App\Http\Enums\UserBalanceTypeEnum;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserBalanceController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $user = (new UserController())->firstOrCreate($request);

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
}
