<?php

namespace App\Http\Controllers\Actions\Collaborate;

use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Requests\Actions\ActionAreaRequest;
use App\Http\ResourceActions\Actions\ArchiveActionArea;
use App\Http\ResourceActions\Actions\RestoreActionArea;
use App\Models\Actions\ActionArea;
use App\Traits\UsesArchivedTableFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ActionAreaController extends CollaborateController
{
    use UsesArchivedTableFilter;

    /** @var string */
    protected string $sortBy = 'title';

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
     * Get form request to be used when validating the input.
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return ActionAreaRequest::class;
    }

    /**
     * Get the model columns that should be displayed in the index table.
     *
     * Use the dot notation for relations and for custom results use functions that
     * will receive the current row as an arg. The field name will be used as the
     * translation key but when you use functions, make sure the array is keyed with the
     * translation name.
     *
     * e.g. ['profile' => 'profile.id', 'name'].
     *
     * @return array<string, mixed>
     */
    protected static function indexColumns(): array
    {
        return [
            'id' => fn ($row) => $row->id,
            'title' => fn ($row) => static::renderPartial($row, 'title-column'),
            'subject' => fn ($row) => $row->subjectCategory->display_label ?? '-',
            'control' => fn ($row) => $row->controlCategory->display_label ?? '-',
            'works' => fn ($row) => static::renderLink(route('collaborate.works.index', ['actionAreas' => [$row->id]]), __('corpus.work.works')),
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
        return parent::baseQuery($request)->with(['controlCategory', 'subjectCategory']);
    }

    /**
     * Get extra data to be sent back to the form views.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    protected function formViewsData(Request $request): array
    {
        return [
            'formWidth' => 'max-w-3xl',
        ];
    }

    /**
     * Get extra data to be sent back to the create view.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    protected function createViewData(Request $request): array
    {
        return $this->formViewsData($request);
    }

    /**
     * Get extra data to be sent back to the edit view.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    protected function editViewData(Request $request): array
    {
        return $this->formViewsData($request);
    }

    /**
     * Get extra data to be sent back to the show view.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    protected function showViewData(Request $request): array
    {
        return $this->formViewsData($request);
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceActions(): array
    {
        return [
            new ArchiveActionArea(),
            new RestoreActionArea(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function resourceFilters(): array
    {
        return [
            ...$this->archivedTableFilter(),
        ];
    }
}
