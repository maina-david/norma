<?php

namespace App\Jobs\Customer;

use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ImplementPasswordResetPolicy implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** @var int */
    public $timeout = 960;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Organisation::where('settings->policies->password_reset->enabled', true)
            ->chunk(100, function (Collection $organisations) {
                $organisations->each(function (Organisation $org) {
                    $lastImplemented = Arr::get($org->settings, 'policies.password_reset.last_implemented');
                    $frequency = Arr::get($org->settings, 'policies.password_reset.frequency');

                    $diff = (int) Carbon::now()->diffInMonths(Carbon::parse($lastImplemented), true);

                    if ($frequency != 0 && (!$lastImplemented || $diff >= $frequency)) {
                        User::inOrganisation($org->id)->chunk(500, function (Collection $users) {
                            $users->each->update(['password_reset' => true]);
                        });
                    }

                    $org->updateSetting('policies.password_reset.last_implemented', Carbon::now());
                });
            });
    }
}
