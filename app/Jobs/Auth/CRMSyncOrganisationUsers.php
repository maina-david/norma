<?php

namespace App\Jobs\Auth;

use App\Http\Services\Hubspot\Actions\UserToHubspotContact;
use App\Http\Services\Hubspot\Contacts;
use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CRMSyncOrganisationUsers implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param Organisation $organisation
     */
    public function __construct(private Organisation $organisation)
    {
        $this->onQueue('libryo-app');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!config('services.hubspot.enabled')) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        /** @var Builder $query */
        $query = User::inOrganisation($this->organisation->id)->hubspotSyncable();

        UserToHubspotContact::eagerLoadFields($query)
            ->chunk(5, function ($users) {
                /** @var \Illuminate\Database\Eloquent\Collection<User> $users */
                Contacts::bulkCreateOrUpdate($users);

                sleep(1);
            });
    }
}
