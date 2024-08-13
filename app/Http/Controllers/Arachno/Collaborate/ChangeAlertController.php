<?php

namespace App\Http\Controllers\Arachno\Collaborate;

use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Controllers\Traits\HasShowAction;
use App\Models\Arachno\ChangeAlert;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ChangeAlertController extends CollaborateController
{
    use HasShowAction;

    /** @var string */
    protected string $sortBy = 'id';

    /** @var string */
    protected string $sortDirection = 'desc';

    /** @var bool */
    protected bool $withCreate = false;

    /** @var bool */
    protected bool $withDelete = false;

    /** @var bool */
    protected bool $withUpdate = false;

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return ChangeAlert::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.change-alerts';
    }

    /**
     * @codeCoverageIgnore
     * Get form request to be used when validating the input.
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return '';
    }

    /**
     * @return array<string, mixed>
     */
    protected static function indexColumns(): array
    {
        return [
            'date' => fn ($row) => $row->created_at,
            'title' => fn ($row) => static::renderPartial($row, 'title-column'),
            'url' => fn ($row) => $row->url,
            'wachete' => fn ($row) => static::renderPartial($row, 'wachete-column'),
            'identify_updates' => fn ($row) => static::renderPartial($row, 'updates-column'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function indexViewData(Request $request): array
    {
        return [
            'fluid' => true,
            'paginate' => 50,
        ];
    }

    /**
     * Generate a new query builder instance for the resource.
     *
     * @param Request $request
     *
     * @return Builder
     */
    protected function baseQuery(Request $request): Builder
    {
        /** @var Builder */
        return parent::baseQuery($request)
            ->with(['work.expressions'])
            ->orderBy('id', 'desc');
    }
}
