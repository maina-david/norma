<?php

namespace App\Policies\Storage\My;

use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Storage\My\File;
use Illuminate\Auth\Access\HandlesAuthorization;

class FilePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     *
     * @return mixed
     */
    // public function viewAny(User $user)
    // {
    // }

    /**
     * @codeCoverageIgnore
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param File $file
     *
     * @return mixed
     */
    public function view(User $user, File $file)
    {
        if ($user->can('storage.my.file.viewAny')) {
            return true;
        }

        if ($file->isGlobal() || $file->isSystem()) {
            return true;
        }

        if (!$file->folder) {
            return false;
        }

        if ($file->isNorma()) {
            if ($file->norma) {
                return Norma::whereKey($file->norma->id)
                    ->userHasAccess($user)
                    ->exists();
            }

            return true;
        }

        if ($file->isOrganisation()) {
            if ($file->organisation) {
                return $user->organisations()->whereKey($file->organisation_id)->exists()
                    || $file->organisation->normas()->userHasAccess($user)->exists();
            }

            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     *
     * @return mixed
     */
    // public function create(User $user)
    // {
    // }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param File $file
     *
     * @return mixed
     */
    public function update(User $user, File $file)
    {
        return $this->delete($user, $file);
    }

    /**
     * @codeCoverageIgnore
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param File $file
     *
     * @return mixed
     */
    public function delete(User $user, File $file)
    {
        if ($user->isMySuperUser()) {
            return true;
        }

        if ($file->isGlobal() || $file->isSystem()) {
            return $user->can('my.storage.file.global.manage');
        }

        if (!$file->folder) {
            return false;
        }

        if ($file->isNorma()) {
            if ($file->norma) {
                return $file->author_id === $user->id
                    || Norma::whereKey($file->norma->id)
                        ->userHasAccess($user)
                        ->whereHas('organisation', fn ($builder) => $builder->userIsOrganisationAdmin($user))
                        ->exists();
            }

            return false;
        }

        if ($file->isOrganisation()) {
            return $file->organisation
                && ($file->author_id === $user->id || $user->isOrganisationAdmin($file->organisation));
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param File $file
     *
     * @return mixed
     */
    // public function restore(User $user, File $file)
    // {
    // }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param File $file
     *
     * @return mixed
     */
    // public function forceDelete(User $user, File $file)
    // {
    // }
}
