<?php

namespace App\Jobs\Customer;

use App\Models\Customer\Organisation;
use App\Services\Auth\UserLifecycleService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SoftDeleteOrganisation implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @param Organisation $organisation
     */
    public function __construct(protected Organisation $organisation)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $lifecycleService = app(UserLifecycleService::class);

        $this->organisation->users()
            ->with(['organisations' => fn ($query) => $query->where('id', '!=', $this->organisation->id)->select(['id'])])
            ->cursor()
            ->each(function ($user) use ($lifecycleService) {
                if ($user->organisations->isEmpty()) {
                    $lifecycleService->deactivate($user);
                }
            });

        $this->organisation->load(['libryos']);

        foreach ($this->organisation->libryos as $libryo) {
            $libryo->delete();
        }
    }
}
