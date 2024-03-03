<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Nwilging\LaravelDiscordBot\Contracts\Services\DiscordInteractionServiceContract;
use Nwilging\LaravelDiscordBot\Support\Commands\Options\StringOption;
use Nwilging\LaravelDiscordBot\Support\Commands\SlashCommand;
use Nwilging\LaravelDiscordBot\Support\Commands\Options\ChannelOption;
use Nwilging\LaravelDiscordBot\Contracts\Services\DiscordApplicationCommandServiceContract;

class DiscordInteractionController
{
    protected DiscordInteractionServiceContract $interactionService;

    public function __construct(DiscordInteractionServiceContract $interactionService)
    {
        $this->interactionService = $interactionService;
    }

    public function handleDiscordInteraction(Request $request)
    {
        Log::info('interaction');
        Log::debug($request);
        $response = $this->interactionService->handleInteractionRequest($request);
        Log::debug(json_encode($response));
        return response()->json($response->toArray(), $response->getStatus());
    }
}
