<?php

namespace App\Http\Controllers\Bookmarks;

use App\Enums\Auth\UserActivityType;
use App\Events\Auth\UserActivity\GenericActivity;
use App\Models\Auth\User;
use App\Models\Corpus\Reference;
use App\Services\Customer\ActiveNormasManager;
use Illuminate\Database\Eloquent\Model;

class ReferenceBookmarkController extends BookmarkController
{
    /**
     * {@inheritDoc}
     */
    protected function routeParamKey(): string
    {
        return 'reference';
    }

    /**
     * {@inheritDoc}
     */
    protected function routeName(): string
    {
        return 'my.reference.bookmarks';
    }

    /**
     * {@inheritDoc}
     */
    protected function related(): string
    {
        return Reference::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function turboPrefix(): string
    {
        return 'reference';
    }

    /**
     * {@inheritDoc}
     */
    protected function postStore(Model $model, User $user): void
    {
        /** @var Reference $model */
        $manager = app(ActiveNormasManager::class);

        event(new GenericActivity(
            $user,
            UserActivityType::bookmarkedLegislation(),
            ['id' => $model->id],
            $manager->getActive(),
            $manager->getActiveOrganisation()
        ));
    }
}
