<?php

namespace App\Http\Controllers;

use App\Http\Enums\RaffleStatusEnum;
use App\Models\Raffle;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Js;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function updateOrCreate(Request $request): User
    {
        $validator = Validator::make($request->post('user'), [
            'discord_id' => 'required|integer',
        ]);

        if($validator->fails()) {
            throw new HttpResponseException(
                response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY)
            );
        }

        $discordId = $request->post('user')['discord_id'];

        try {
            $user = User::updateOrCreate(
                [
                    'oauth_type' => 'discord',
                    'oauth_id' => $discordId
                ],
                [
                    'name' => $request->post('user')['name'],
                    'avatar_url' => $request->post('user')['avatar_url'],
                    'password' => encrypt($discordId)
                ]
            );
        } catch (\Exception $e) {
            Log::error('Can\'t create or update user by discord id: ' . $discordId);
            Log::error($e->getMessage());
            throw new HttpResponseException(
                response()->json(['message' => 'Ошибка при создании пользователя.'], Response::HTTP_NOT_FOUND)
            );
        }

        return $user;
    }

    public function activeRaffles(User $user): Collection
    {
        return Raffle::with([
                'tickets' => function($query) use ($user) {
                    return $query->where('user_id', '=', $user->id);
                }
            ])
            ->where('is_published', '=', 1)
            ->where('status', '=', RaffleStatusEnum::ACTIVE->value)
            ->get();
    }

    public function profile(User $user): JsonResponse
    {

    }
}
