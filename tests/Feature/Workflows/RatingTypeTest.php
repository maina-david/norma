<?php

namespace Tests\Feature\Workflows;

use App\Models\Workflows\RatingType;
use Tests\Feature\Abstracts\CrudTestCase;

class RatingTypeTest extends CrudTestCase
{
    /** @var string */
    protected string $sortBy = 'name';

    protected bool $collaborate = true;

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return RatingType::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.rating-types';
    }

    /**
     * The list of database columns that should be visible on the pages.
     *
     * @return string[]
     */
    protected static function visibleFields(): array
    {
        return ['name', 'description'];
    }

    /**
     * The list of database columns that should be visible on the pages with forms.
     *
     * @return string[]
     */
    protected static function visibleLabels(): array
    {
        return ['Name', 'Description', 'Course Link'];
    }
}
