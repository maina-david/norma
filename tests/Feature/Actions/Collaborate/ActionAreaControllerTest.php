<?php

namespace Tests\Feature\Actions\Collaborate;

use App\Models\Actions\ActionArea;
use Tests\Feature\Abstracts\CrudTestCase;
use Tests\Feature\Traits\HasArchivingActions;

class ActionAreaControllerTest extends CrudTestCase
{
    use HasArchivingActions;

    /** @var string */
    protected string $sortBy = 'title';

    /** @var bool */
    protected bool $collaborate = true;

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return ActionArea::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.action-areas';
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
        return [
            'Control',
            'Subject',
        ];
    }

    /**
     * Get the labels that are visible on the show page.
     *
     * @return array<int, string>
     */
    protected static function visibleShowLabels(): array
    {
        return ['Title'];
    }
}
