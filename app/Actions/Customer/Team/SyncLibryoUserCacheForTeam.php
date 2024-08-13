<?php

namespace App\Actions\Customer\Team;

use App\Actions\Auth\User\SyncLibryoUserCache;
use App\Models\Customer\Team;
use App\Stores\Customer\LibryoUserStore;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncLibryoUserCacheForTeam implements ShouldBeUnique
{
    use AsAction;

    public string $jobQueue = 'libryo-app';

    public int $jobUniqueFor = 120; // 120 seconds

    public function __construct(protected LibryoUserStore $libryoUserStore)
    {
    }

    public function handle(Team $team): void
    {
        foreach ($team->users as $user) {
            SyncLibryoUserCache::dispatch($user->id);
        }
    }

    public function asJob(int $teamId): void
    {
        /** @var Team */
        $team = Team::with('users')->findOrFail($teamId);
        $this->handle($team);
    }

    public function getJobUniqueId(int $teamId): string
    {
        return static::class . '_' . $teamId;
    }
}
