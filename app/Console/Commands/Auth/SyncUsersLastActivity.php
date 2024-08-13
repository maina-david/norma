<?php

namespace App\Console\Commands\Auth;

use App\Http\Services\Hubspot\Actions\UserToHubspotContact;
use App\Http\Services\Hubspot\Contacts;
use App\Models\Auth\HubspotId;
use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class SyncUsersLastActivity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'libryo:sync-activities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Updates the CRM with the user's activity information.";

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        if (!config('services.hubspot.enabled')) {
            // @codeCoverageIgnoreStart
            return 0;
            // @codeCoverageIgnoreEnd
        }

        /** @var Builder $query */
        $query = User::hubspotSyncable();

        UserToHubspotContact::eagerLoadFields($query)
            ->whereHas('activities', function ($builder) {
                $builder->where('created_at', '>=', Carbon::now()->startOfYear());
            })
            ->chunk(10, function ($users) {
                /** @var \Illuminate\Database\Eloquent\Collection<\App\Models\Auth\User> $users */
                $response = Contacts::bulkCreateOrUpdate($users) ? 'Successfully processed' : 'Error processing';

                $this->line("{$response} batch of 10.");

                sleep(1);
            });

        User::where('active', false)->has('hubspot')
            ->pluck('id')
            ->chunk(1000)
            ->each(fn ($ids) => HubspotId::whereIn('user_id', $ids->all())->delete());

        return 0;
    }
}
