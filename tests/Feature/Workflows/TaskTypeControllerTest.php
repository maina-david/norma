<?php

namespace Tests\Feature\Workflows;

use App\Models\Workflows\TaskType;
use Tests\Feature\Abstracts\CrudTestCase;

class TaskTypeControllerTest extends CrudTestCase
{
    protected string $sortBy = 'name';

    protected bool $collaborate = true;

    /**
     * {@inheritDoc}
     */
    protected static function resource(): string
    {
        return TaskType::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.task-types';
    }

    /**
     * {@inheritDoc}
     */
    protected static function visibleFields(): array
    {
        return [
            'name', 'ratingGroup.name',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected static function visibleLabels(): array
    {
        return [
            'Name', 'Rating Group', 'Navigates To',
        ];
    }
}
