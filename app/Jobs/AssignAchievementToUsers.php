<?php

namespace App\Jobs;

use App\Models\Achievement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssignAchievementToUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Achievement $achievement
    ){}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $existedUsers = $this->achievement->users()->pluck('user_id');

        DB::table('users')
            ->select('id')
            ->whereNotIn('id', $existedUsers)
            ->orderByDesc('updated_at')
            ->chunk(100, function (Collection $users) use(&$existedUsers) {
                foreach ($users as $user) {
                    $this->achievement->users()->create(['user_id' => $user->id]);
                }

                $existedUsers = $this->achievement->users()->pluck('user_id');
            });
    }
}
