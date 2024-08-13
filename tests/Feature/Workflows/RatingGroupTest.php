<?php

namespace Tests\Feature\Workflows;

use App\Models\Workflows\RatingGroup;
use App\Models\Workflows\RatingType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Feature\Abstracts\CrudTestCase;

class RatingGroupTest extends CrudTestCase
{
    /** @var string */
    protected string $sortBy = 'name';

    protected bool $searchable = true;

    protected bool $collaborate = true;

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return RatingGroup::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.rating-groups';
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
        return ['Name', 'Description'];
    }

    protected static function preCreate(Factory $factory, string $route): Factory
    {
        if ($route === 'store') {
            return $factory->state([
                'rating_types' => RatingType::factory()->count(2)->create()->pluck('id')->toArray(),
            ]);
        }

        return $factory;
    }
}
