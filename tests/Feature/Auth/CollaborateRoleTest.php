<?php

namespace Tests\Feature\Auth;

use App\Models\Auth\Role;
use App\Models\Workflows\TaskType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Feature\Abstracts\CrudTestCase;

class CollaborateRoleTest extends CrudTestCase
{
    /** @var string */
    protected string $sortBy = 'title';

    protected bool $collaborate = true;

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return Role::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.roles';
    }

    /**
     * The list of database columns that should be visible on the pages.
     *
     * @return string[]
     */
    protected static function visibleFields(): array
    {
        return [];
    }

    /**
     * The list of database columns that should be visible on the pages with forms.
     *
     * @return string[]
     */
    protected static function visibleLabels(): array
    {
        return ['Title', 'Sources', 'Users', 'Roles & Permissions'];
    }

    /**
     * Perform tasks before creation of the test model.
     *
     * @param Factory $factory
     * @param string  $route
     *
     * @return Factory
     */
    protected static function preCreate(Factory $factory, string $route): Factory
    {
        TaskType::factory()->create();

        return $factory->collaborate();
    }
}
