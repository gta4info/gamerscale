<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Nwilging\LaravelDiscordBot\Contracts\Services\DiscordInteractionServiceContract;

class DiscordInteractionController
{
    protected DiscordInteractionServiceContract $interactionService;

    public function __construct(DiscordInteractionServiceContract $interactionService)
    {
        $this->interactionService = $interactionService;
    }

    public function handleDiscordInteraction(Request $request)
    {
        $response = $this->interactionService->handleInteractionRequest($request);

        $member = $request->post('member')['user'];
        $user = User::firstOrCreate(
            [
                'oauth_type' => 'discord',
                'oauth_id' => $member['id']
            ],
            [
                'oauth_id' => $member['id'],
                'oauth_type' => 'discord',
                'password' => encrypt($member['id'])
            ]
        );

        Log::info($user);

        return response()->json($response->toArray(), $response->getStatus());
    }
}
