<?php

namespace App\Http\ResourceActions\Actions;

use App\Contracts\Http\ResourceAction;
use App\Http\ResourceActions\RestoresArchivedResources;
use App\Models\Actions\ActionArea;
use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Builder;

class RestoreActionArea implements ResourceAction
{
    use RestoresArchivedResources;

    /**
     * {@inheritDoc}
     */
    protected function baseQuery(): Builder
    {
        return (new ActionArea())->newQuery();
    }

    /**
     * Check if the user is authorised to perform the action.
     *
     * @param User|null $user
     *
     * @return bool
     */
    public function authorise(?User $user): bool
    {
        return $user && $user->can('collaborate.actions.action-area.archive');
    }
}
