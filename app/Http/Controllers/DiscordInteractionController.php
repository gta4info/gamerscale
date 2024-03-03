<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        return response()->json($response->toArray(), $response->getStatus());
    }
}
