<?php

namespace App\Http\Controllers\Bookmarks;

use App\Models\Assess\AssessmentItem;

class AssessmentItemBookmarkController extends BookmarkController
{
    /**
     * {@inheritDoc}
     */
    protected function routeParamKey(): string
    {
        return 'item';
    }

    /**
     * {@inheritDoc}
     */
    protected function routeName(): string
    {
        return 'my.assess.bookmarks';
    }

    /**
     * {@inheritDoc}
     */
    protected function related(): string
    {
        return AssessmentItem::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function turboPrefix(): string
    {
        return 'assess';
    }
}
