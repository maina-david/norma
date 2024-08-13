<?php

namespace App\Http\Controllers\Bookmarks;

use App\Events\Auth\UserActivity\LegalUpdates\LegalUpdateBookmarked;
use App\Models\Auth\User;
use App\Models\Notify\LegalUpdate;
use App\Services\Customer\ActiveLibryosManager;
use Illuminate\Database\Eloquent\Model;

class LegalUpdateBookmarkController extends BookmarkController
{
    /**
     * {@inheritDoc}
     */
    protected function routeParamKey(): string
    {
        return 'update';
    }

    /**
     * {@inheritDoc}
     */
    protected function routeName(): string
    {
        return 'my.legal-update.bookmarks';
    }

    /**
     * {@inheritDoc}
     */
    protected function related(): string
    {
        return LegalUpdate::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function turboPrefix(): string
    {
        return 'legal-update';
    }

    /**
     * Perform action after storage.
     *
     * @param LegalUpdate           $model
     * @param \App\Models\Auth\User $user
     *
     * @return void
     */
    protected function postStore(Model $model, User $user): void
    {
        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);

        event(new LegalUpdateBookmarked($model, $user, $manager->getActive(), $manager->getActiveOrganisation()));
    }
}
