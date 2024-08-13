<?php

namespace Tests\Feature\System;

use App\Models\System\SystemNotification;
use Tests\Feature\Abstracts\CrudTestCase;

class SystemNotificationTest extends CrudTestCase
{
    /** @var string */
    protected string $sortBy = 'id';

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return SystemNotification::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'admin.system-notifications';
    }

    /**
     * The list of database columns that should be visible on the pages.
     *
     * @return string[]
     */
    protected static function visibleFields(): array
    {
        return [
            'title',
        ];
    }

    /**
     * The list of database columns that should be visible on the pages with forms.
     *
     * @return string[]
     */
    protected static function visibleLabels(): array
    {
        return [
            'Title', 'Alert Type', 'Content', 'Expiry Date', 'Is Permanent', 'Active', 'Has User Action',
            'User Action Text', 'User Action Link',
        ];
    }

    /**
     * A list of actions to exclude from testing.
     * Options: index, create, store, edit, update, destroy.
     *
     * @return array<int, string>
     */
    protected static function excludeActions(): array
    {
        return ['show'];
    }
}
