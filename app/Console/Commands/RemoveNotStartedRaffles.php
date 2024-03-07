<?php

namespace App\Console\Commands;

use App\Models\Raffle;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RemoveNotStartedRaffles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:remove-not-started-raffles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove raffles with flag is_published = false';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Raffle::where('is_published', '=', 0)
            ->where('start_at', '<', Carbon::now()->subMinutes(30))
            ->delete();
    }
}
