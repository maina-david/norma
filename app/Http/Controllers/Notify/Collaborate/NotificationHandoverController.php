<?php

namespace App\Http\Controllers\Notify\Collaborate;

use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Requests\Notify\NotificationHandoverRequest;
use App\Models\Notify\NotificationHandover;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class NotificationHandoverController extends CollaborateController
{
    /**
     * {@inheritDoc}
     */
    protected static function resource(): string
    {
        return NotificationHandover::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.notification-handovers';
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceFormRequest(): string
    {
        return NotificationHandoverRequest::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function indexColumns(): array
    {
        return [
            'id' => fn ($row) => $row->id,
            'text' => fn ($row) => $row->text,
            'workflow' => fn ($row) => $row->board->title ?? '-',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function baseQuery(Request $request): Builder
    {
        return parent::baseQuery($request)->with(['board']);
    }
}
