<?php

namespace App\Events;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Nwilging\LaravelDiscordBot\Events\MessageComponentInteractionEvent;
use Nwilging\LaravelDiscordBot\Contracts\Listeners\MessageComponentInteractionEventListenerContract;

class MessageComponentInteractionListener implements MessageComponentInteractionEventListenerContract, ShouldQueue
{
    public function replyContent(MessageComponentInteractionEvent $event): ?string
    {
        // return null; - to override and send no reply
        return 'my reply message';
    }

    public function behavior(MessageComponentInteractionEvent $event): int
    {
        // return static::LOAD_WHILE_HANDLING; // Shows a loading message/status while handling
        // return static::REPLY_TO_MESSAGE; // Replies to the interaction with replyContent(). Required if you want to reply to the interaction
        return static::DEFER_WHILE_HANDLING; // Shows no loading message/status while handling
    }

    public function handle(MessageComponentInteractionEvent $event): void
    {
        // Handle the event like a normal listener
        Log::info(123123);
        Log::debug(json_encode($event));
    }
}
