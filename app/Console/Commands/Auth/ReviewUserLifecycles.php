<?php

namespace App\Console\Commands\Auth;

use App\Jobs\Auth\SendOnboardingEmails;
use App\Jobs\Auth\UpdateLifecycles;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class ReviewUserLifecycles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'libryo:review-lifecycles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handle the review of all users and their lifecycle stages';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Bus::chain([
            new UpdateLifecycles(),
            new SendOnboardingEmails(),
        ])->dispatch();

        return 0;
    }
}
