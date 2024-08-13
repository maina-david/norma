<?php

namespace App\Http\ResourceActions\Workflows;

use App\Contracts\Http\ResourceAction;
use App\Http\ResourceActions\ArchivesResources;
use App\Models\Auth\User;
use App\Models\Workflows\Board;
use Illuminate\Database\Eloquent\Builder;

class ArchiveBoard implements ResourceAction
{
    use ArchivesResources;

    /**
     * {@inheritDoc}
     */
    protected function baseQuery(): Builder
    {
        return (new Board())->newQuery();
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
        return $user && $user->can('collaborate.workflows.board.archive');
    }
}
