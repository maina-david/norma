<?php

namespace App\Jobs\Auth;

use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RemoveTrialUsers implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->onQueue('norma-app');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        User::whereHas('trial', fn ($query) => $query->where('expires_at', '<', Carbon::now()))->forceDelete();
    }
}
