<?php
declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Nwilging\LaravelDiscordBot\Contracts\Listeners\ApplicationCommandInteractionEventListenerContract;
use Nwilging\LaravelDiscordBot\Events\ApplicationCommandInteractionEvent;
use Illuminate\Contracts\Queue\ShouldQueue;

class MessageComponentInteractionListener implements ShouldQueue, ApplicationCommandInteractionEventListenerContract
{
    public function replyContent(ApplicationCommandInteractionEvent $event): ?string
    {
        return 'loading';
    }

    public function behavior(ApplicationCommandInteractionEvent $event): int
    {
        return static::REPLY_TO_MESSAGE;
    }

    public function command(): ?string
    {
        return null;
    }

    public function handle(ApplicationCommandInteractionEvent $event): void
    {
        // handle the interaction
        Log::debug($event->getInteractionRequest()->get('data', []));
    }
}
