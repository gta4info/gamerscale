<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function firstOrCreate(Request $request): User
    {
        $validator = Validator::make($request->all(), [
            'discord_id' => 'required|integer',
        ]);

        if($validator->fails()) {
            throw new HttpResponseException(
                response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY)
            );
        }

        $discordId = $request->post('discord_id');

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
        } catch (\Exception $e) {
            Log::error('Can\'t create or find user by discord id: ' . $discordId);
            Log::error($e->getMessage());
            throw new HttpResponseException(
                response()->json(['message' => 'Ошибка при создании пользователя.'], Response::HTTP_NOT_FOUND)
            );
        }

        return $user;
    }
}
