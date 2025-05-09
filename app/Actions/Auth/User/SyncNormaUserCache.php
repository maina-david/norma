<?php

namespace App\Actions\Auth\User;

use App\Models\Auth\User;
use App\Stores\Customer\NormaUserStore;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Decorators\JobDecorator;

class SyncNormaUserCache implements ShouldBeUnique
{
    use AsAction;

    public int $jobUniqueFor = 30; // 30 seconds

    public function __construct(protected NormaUserStore $normaUserStore)
    {
    }

    public function handle(User $user): void
    {
        $this->normaUserStore->syncFromTeams($user);
    }

    public function configureJob(JobDecorator $job): void
    {
        $job->onQueue('caching')
            ->delay(30); // delay it slightly so it's not syncing while more changes are being made
    }

    public function asJob(int $userId): void
    {
        /** @var User */
        $user = User::findOrFail($userId);
        $this->handle($user);
    }

    public function getJobUniqueId(int $userId): string
    {
        return static::class . '_' . $userId;
    }
}
