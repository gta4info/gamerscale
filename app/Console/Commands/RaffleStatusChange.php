<?php

namespace App\Console\Commands;

use App\Http\Controllers\RaffleController;
use App\Http\Enums\RaffleStatusEnum;
use App\Models\Raffle;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RaffleStatusChange extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:raffle-status-change';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change status for raffles based on time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $raffles = Raffle::where('is_published', '=', 1)
            ->where('status', '!=', RaffleStatusEnum::COMPLETED->value)
            ->get();

        foreach ($raffles as $raffle) {
            $status = $raffle->status;

            if($raffle->start_at <= Carbon::now()->toDateTimeString()) {
                $status = RaffleStatusEnum::ACTIVE->value;
            }

            if($raffle->end_at <= Carbon::now()->toDateTimeString()) {
                $status = RaffleStatusEnum::COMPLETED->value;

                /** If new status is completed then we need to draw the raffle */
                (new RaffleController())->draw($raffle);
            }

            if($status !== $raffle->status) {
                $raffle->update(['status' => $status]);
            }
        }
    }
}
