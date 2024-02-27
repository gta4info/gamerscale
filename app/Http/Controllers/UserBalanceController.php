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
        $discordId = $request->post('discordId');

        try {
            $user = User::firstOrCreate(
                [
                    'oauth_type' => 'discord',
                    'oauth_id' => $discordId
                ],
                [
                    'oauth_id' => $discordId,
                    'oauth_type' => 'discord',
                    'password' => encrypt($discordId)
                ]
            );

            if(!$user) {
                Log::error(json_encode([
                    "Can't find or create user with discord id = $discordId"
                ]));
                return response()->json([
                    'message' => 'User not found',
                    'status' => 404
                ], 404);
            }

            /** Check and update fiat */
            $lastFiatAmount = $this->findUserLastBalanceRecordByType($user, UserBalanceTypeEnum::FIAT->value);
            $fiatAmount = (float)$request->post('fiat');

            if($fiatAmount != $lastFiatAmount) {
                $user->balance()->create([
                    'amount' => $fiatAmount,
                    'type' => UserBalanceTypeEnum::FIAT->value
                ]);
            }

            /** Check and update VBucks */
            $lastVbucksAmount = $this->findUserLastBalanceRecordByType($user, UserBalanceTypeEnum::VBUCKS->value);
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

    public function findUserLastBalanceRecordByType(User $user, int $type): float|null
    {
        return (float)$user->balance()
            ->where('type', '=', $type)
            ->latest()
            ->value('amount') ?? null;
    }
}
