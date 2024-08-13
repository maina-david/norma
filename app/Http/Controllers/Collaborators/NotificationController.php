<?php

namespace App\Http\Controllers\Collaborators;

use App\Enums\Application\ApplicationType;
use App\Http\Controllers\Abstracts\CrudController;
use App\Models\Collaborators\Collaborator;
use App\Models\Collaborators\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends CrudController
{
    protected bool $searchable = false;
    protected bool $withCreate = false;
    protected bool $withShow = false;
    protected bool $withUpdate = false;
    protected bool $withDelete = false;

    /**
     * {@inheritDoc}
     */
    public static function shouldAuthorise(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    protected static function application(): ApplicationType
    {
        return ApplicationType::collaborate();
    }

    /**
     * {@inheritDoc}
     */
    protected static function resource(): string
    {
        return Notification::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.collaborators.notifications';
    }

    /**
     * @codeCoverageIgnore
     * {@inheritDoc}
     */
    protected static function resourceFormRequest(): string
    {
        // @phpstan-ignore-next-line
        return '';
    }

    protected function baseQuery(Request $request): Builder
    {
        /** @var Collaborator $user */
        $user = Collaborator::withoutGlobalScope('collaborateUser')->find(Auth::id());

        return $user->notifications()->getQuery();
    }

    /**
     * {@inheritDoc}
     */
    protected static function indexColumns(): array
    {
        return [
            'details' => fn ($row) => static::renderPartial($row, 'details-column'),
            'date_created' => fn ($row) => view('partials.ui.data-table.date-column', ['date' => $row->created_at])->render(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function indexViewData(Request $request): array
    {
        return [
            'headings' => false,
        ];
    }
}
