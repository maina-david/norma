<?php

namespace App\Actions\Customer\Team;

use App\Actions\Auth\User\SyncNormaUserCache;
use App\Models\Customer\Team;
use App\Stores\Customer\NormaUserStore;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncNormaUserCacheForTeam implements ShouldBeUnique
{
    use AsAction;

    public string $jobQueue = 'norma-app';

    public int $jobUniqueFor = 120; // 120 seconds

    public function __construct(protected NormaUserStore $normaUserStore)
    {
    }

    public function handle(Team $team): void
    {
        foreach ($team->users as $user) {
            SyncNormaUserCache::dispatch($user->id);
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
