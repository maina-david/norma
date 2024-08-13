<?php

namespace App\Http\ResourceActions\Assess;

use App\Contracts\Http\ResourceAction;
use App\Http\ResourceActions\ArchivesResources;
use App\Models\Assess\AssessmentItem;
use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Builder;

class ArchiveAssessmentItem implements ResourceAction
{
    use ArchivesResources;

    /**
     * {@inheritDoc}
     */
    protected function baseQuery(): Builder
    {
        return (new AssessmentItem())->newQuery();
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
        return $user && $user->can('collaborate.assess.assessment-item.archive');
    }
}
