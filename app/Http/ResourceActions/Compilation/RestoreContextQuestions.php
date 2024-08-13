<?php

namespace App\Http\ResourceActions\Compilation;

use App\Contracts\Http\ResourceAction;
use App\Http\ResourceActions\RestoresArchivedResources;
use App\Models\Auth\User;
use App\Models\Compilation\ContextQuestion;
use Illuminate\Database\Eloquent\Builder;

class RestoreContextQuestions implements ResourceAction
{
    use RestoresArchivedResources;

    /**
     * {@inheritDoc}
     */
    protected function baseQuery(): Builder
    {
        return (new ContextQuestion())->newQuery();
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
        return $user && $user->can('collaborate.compilation.context-question.archive');
    }
}
