<?php

namespace App\Stores\Notify;

use App\Events\Notify\LibraryAttachedToLegalUpdate;
use App\Events\Notify\NormaAttachedToLegalUpdate;
use App\Events\Notify\UserAttachedToLegalUpdate;
use App\Models\Auth\User;
use App\Models\Compilation\Library;
use App\Models\Customer\Norma;
use App\Models\Notify\LegalUpdate;
use App\Stores\Traits\AttachesDetaches;
use Illuminate\Support\Collection;

class LegalUpdateStore
{
    use AttachesDetaches;

    /**
     * Attach libraries to the legal update.
     *
     * @param LegalUpdate             $update
     * @param Collection<Library|int> $libraries
     *
     * @return void
     */
    public function attachLibraries(LegalUpdate $update, Collection $libraries): void
    {
        $this->attachRelations($update, 'libraries', $libraries, [], function ($library) use ($update) {
            LibraryAttachedToLegalUpdate::dispatch($library, $update);
        });
    }

    /**
     * Attach libraries to the legal update.
     *
     * @param LegalUpdate             $update
     * @param Collection<Library|int> $libraries
     *
     * @return void
     */
    public function detachLibraries(LegalUpdate $update, Collection $libraries): void
    {
        $this->detachRelations($update, 'libraries', $libraries);
    }

    /**
     * Attach libraries to the normas.
     *
     * @param LegalUpdate            $update
     * @param Collection<Norma|int> $normas
     *
     * @return void
     */
    public function attachNormas(LegalUpdate $update, Collection $normas): void
    {
        $this->attachRelations($update, 'normas', $normas, [], function ($norma) use ($update) {
            NormaAttachedToLegalUpdate::dispatch($norma, $update);
        });
    }

    /**
     * Attach libraries to the users.
     *
     * @param LegalUpdate          $update
     * @param Collection<User|int> $users
     *
     * @return void
     */
    public function attachUsers(LegalUpdate $update, Collection $users): void
    {
        $this->attachRelations($update, 'users', $users, [], function ($user) use ($update) {
            UserAttachedToLegalUpdate::dispatch($user, $update);
        });
    }
}
