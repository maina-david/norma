<?php

namespace App\Http\Controllers\System\Admin;

use App\Http\Controllers\Abstracts\Admin\AdminController;
use App\Http\Requests\System\SystemNotificationRequest;
use App\Models\System\ApiLog;

/**
 * @codeCoverageIgnore
 */
class ApiLogController extends AdminController
{
    /** @var string */
    protected string $sortBy = 'id';

    /** @var string */
    protected string $sortDirection = 'desc';

    /** @var bool */
    protected bool $sortable = false;

    /** @var bool */
    protected bool $searchable = false;

    /** @var bool */
    protected bool $withCreate = false;

    /** @var bool */
    protected bool $withShow = false;

    /** @var bool */
    protected bool $withUpdate = false;

    /** @var bool */
    protected bool $withDelete = false;

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return ApiLog::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'admin.api-logs';
    }

    /**
     * Get form request to be used when validating the input.
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return SystemNotificationRequest::class;
    }

    /**
     * Get the model columns that should be displayed in the index table. Use the dot notation for relations.
     * e.g. ['profile.id', 'name'].
     *
     * @return array<int|string, mixed>
     */
    protected static function indexColumns(): array
    {
        return [
            'id',
            'route',
            'method',
            'query',
            'headers' => fn ($row) => print_r($row->headers, true),
            'body',
            'user_id',
            'response_status',
            'created_at',
        ];
    }
}
