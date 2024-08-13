<?php

namespace App\Policies\Workflows;

use App\Models\Auth\User;
use App\Models\Workflows\Note;
use Illuminate\Auth\Access\HandlesAuthorization;

class NotePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user)
    {
        return $user->can('collaborate.workflows.note.viewAny');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user)
    {
        return $user->can('collaborate.workflows.note.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Note $note
     *
     * @return bool
     */
    public function update(User $user, Note $note)
    {
        return $note->isAuthor($user) || $user->can('collaborate.workflows.note.update');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Note $note
     *
     * @return bool
     */
    public function delete(User $user, Note $note)
    {
        return $note->isAuthor($user) || $user->can('collaborate.workflows.note.delete');
    }
}
