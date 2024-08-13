<?php

namespace Tests\Feature\Arachno\Collaborate;

use App\Models\Arachno\UpdateEmail;
use Tests\Feature\Abstracts\CrudTestCase;

class UpdateEmailControllerTest extends CrudTestCase
{
    /** @var string */
    protected string $sortBy = 'id';
    protected bool $descending = true;

    protected bool $collaborate = true;

    /**
     * A list of actions to exclude from testing.
     * Options: index, create, store, edit, update, destroy.
     *
     * @return array
     */
    protected static function excludeActions(): array
    {
        return [
            'create', 'store', 'edit', 'update', 'destroy',
        ];
    }

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return UpdateEmail::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.update-emails';
    }

    /**
     * The list of database columns that should be visible on the pages.
     *
     * @return string[]
     */
    protected static function visibleFields(): array
    {
        return ['subject', 'to'];
    }

    /**
     * The list of database columns that should be visible on the pages with forms.
     *
     * @return string[]
     */
    protected static function visibleLabels(): array
    {
        return ['Subject', 'To', 'Email Text', 'Email Html'];
    }
}
