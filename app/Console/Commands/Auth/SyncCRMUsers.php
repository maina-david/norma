<?php

namespace App\Console\Commands\Auth;

use App\Http\Services\Hubspot\Actions\UserToHubspotContact;
use App\Http\Services\Hubspot\Contacts;
use App\Models\Auth\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class SyncCRMUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'libryo:sync-crm-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all the client details on the CRM';

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
        $query = User::hubspotSyncable()->orderBy('id');

        UserToHubspotContact::eagerLoadFields($query)
            ->chunk(5, function ($users) {
                /** @var \Illuminate\Database\Eloquent\Collection<\App\Models\Auth\User> $users */
                $response = Contacts::bulkCreateOrUpdate($users) ? 'Successfully processed' : 'Error processing';

                $this->line("{$response} batch of 5.");

                sleep(1);
            });

        return 0;
    }
}
