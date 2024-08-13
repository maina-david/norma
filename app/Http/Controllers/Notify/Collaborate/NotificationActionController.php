<?php

namespace App\Http\Controllers\Notify\Collaborate;

use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Requests\Notify\NotificationActionRequest;
use App\Models\Notify\NotificationAction;

class NotificationActionController extends CollaborateController
{
    /**
     * {@inheritDoc}
     */
    protected static function resource(): string
    {
        return NotificationAction::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.notification-actions';
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceFormRequest(): string
    {
        return NotificationActionRequest::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function indexColumns(): array
    {
        return [
            'id',
            'text',
        ];
    }
}
