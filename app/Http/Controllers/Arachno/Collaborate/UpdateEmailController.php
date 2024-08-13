<?php

namespace App\Http\Controllers\Arachno\Collaborate;

use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Controllers\Traits\HasShowAction;
use App\Models\Arachno\UpdateEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class UpdateEmailController extends CollaborateController
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
            'subject' => fn ($row) => $row->subject,
            'to' => fn ($row) => $row->to,
            'text' => fn ($row) => mb_substr($row->text, 0, 500),
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
            ->orderBy('id', 'desc');
    }

    /**
     * {@inheritDoc}
     */
    protected function showViewData(Request $request): array
    {
        return [
            'formWidth' => 'max-w-7xl',
        ];
    }
}
