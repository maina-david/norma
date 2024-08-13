<?php

namespace Tests\Feature\System\Collaborate;

use App\Models\System\ProcessingJob;
use Tests\Feature\Abstracts\CrudTestCase;

class ProcessingJobTest extends CrudTestCase
{
    /** @var string */
    protected string $sortBy = 'created_at';

    protected bool $descending = true;

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return ProcessingJob::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.processing-jobs';
    }

    /**
     * The list of database columns that should be visible on the pages.
     *
     * @return string[]
     */
    protected static function visibleFields(): array
    {
        return ['id'];
    }

    /**
     * The list of database columns that should be visible on the pages with forms.
     *
     * @return string[]
     */
    protected static function visibleLabels(): array
    {
        return [];
    }

    /**
     * Get the labels that are visible on the show page.
     *
     * @return array<int, string>
     */
    protected static function visibleShowLabels(): array
    {
        return [];
    }

    /**
     * A list of actions to exclude from testing.
     * Options: index, create, store, edit, update, destroy.
     *
     * @return array
     */
    protected static function excludeActions(): array
    {
        return [
            'create', 'show', 'store', 'edit', 'update', 'destroy',
        ];
    }
}
