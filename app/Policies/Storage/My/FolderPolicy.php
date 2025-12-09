<?php

namespace App\Policies\Storage\My;

use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Storage\My\Folder;
use Illuminate\Auth\Access\HandlesAuthorization;

class FolderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param \App\Models\Auth\User $user
     *
     * @return mixed
     */
    // public function viewAny(User $user)
    // {
    //     //
    // }

    /**
     * Determine whether the user can view the model.
     *
     * @param \App\Models\Auth\User         $user
     * @param \App\Models\Storage\My\Folder $folder
     *
     * @return mixed
     */
    // public function view(User $user, Folder $folder)
    // {
    //     //
    // }

    /**
     * Determine whether the user can create models.
     *
     * @param \App\Models\Auth\User $user
     *
     * @return mixed
     */
    // public function create(User $user)
    // {
    //     //
    // }

    /**
     * Determine whether the user can update the model.
     *
     * @param \App\Models\Auth\User         $user
     * @param \App\Models\Storage\My\Folder $folder
     *
     * @return mixed
     */
    // public function update(User $user, Folder $folder)
    // {
    //     //
    // }

    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\Auth\User         $user
     * @param \App\Models\Storage\My\Folder $folder
     *
     * @return mixed
     */
    // public function delete(User $user, Folder $folder)
    // {
    //     //
    // }

    /**
     * Determine whether the user can restore the model.
     *
     * @param \App\Models\Auth\User         $user
     * @param \App\Models\Storage\My\Folder $folder
     *
     * @return mixed
     */
    // public function restore(User $user, Folder $folder)
    // {
    //     //
    // }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param \App\Models\Auth\User         $user
     * @param \App\Models\Storage\My\Folder $folder
     *
     * @return mixed
     */
    // public function forceDelete(User $user, Folder $folder)
    // {
    //     //
    // }

    /**
     * Determines whether a user can upload a file to a norma folder.
     *
     * @param User   $user
     * @param Folder $folder
     * @param Norma $norma
     *
     * @return bool
     */
    public function uploadFileForNorma(User $user, Folder $folder, Norma $norma)
    {
        if (!$folder->isNorma()) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        return (bool) $user->hasNormaAccess($norma);
    }

    /**
     * Determines whether a user can upload a file to an organisation folder.
     *
     * @param User         $user
     * @param Folder       $folder
     * @param Organisation $organisation
     *
     * @return bool
     */
    public function uploadFileForOrganisation(User $user, Folder $folder, Organisation $organisation)
    {
        if (!$folder->isOrganisation()) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        return ((bool) $user->isInOrganisation($organisation)) || $user->isMySuperUser();
    }

    /**
     * Determines whether a user can hide or show the folder in organisation settings.
     *
     * @param User   $user
     * @param Folder $folder
     *
     * @return bool
     */
    public function hideOrShowInOrgSettings(User $user, Folder $folder)
    {
        if (!$folder->isOrganisation() && !$folder->isNorma()) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }
        if (!is_null($folder->organisation_id)) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        return userIsOrgAdmin() || userCanManageAllOrgs();
    }

    /**
     * @param User         $user
     * @param Folder       $folder
     * @param Organisation $organisation
     *
     * @return bool
     */
    public function manageOrganisationFolder(User $user, Folder $folder, Organisation $organisation): bool
    {
        if (!$user->isOrganisationAdmin($organisation) && !userCanManageAllOrgs()) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        return $folder->organisation_id === $organisation->id;
    }
}
