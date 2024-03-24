<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AssignAchievementsToUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:assign-achievements-to-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign missing achievements to users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }
}
