<?php

namespace App\Http\Controllers\Compilation\Collaborate;

use App\Contracts\Http\ResourceAction;
use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Requests\Compilation\ContextQuestionRequest;
use App\Http\ResourceActions\Compilation\ArchiveContextQuestions;
use App\Http\ResourceActions\Compilation\RestoreContextQuestions;
use App\Models\Compilation\ContextQuestion;
use App\Traits\UsesArchivedTableFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ContextQuestionController extends CollaborateController
{
    use UsesArchivedTableFilter;

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return ContextQuestion::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.context-questions';
    }

    /**
     * Get form request to be used when validating the input.
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return ContextQuestionRequest::class;
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
            'question' => fn ($row) => self::renderPartial($row, 'question-column'),
            'works' => fn ($row) => static::renderLink(route('collaborate.works.index', ['questions' => [$row->id]]), __('corpus.work.works')),
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
            ->with(['locations'])
            ->orderBy('prefix')
            ->orderBy('predicate')
            ->orderBy('pre_object')
            ->orderBy('object')
            ->orderBy('post_object');
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
            'formWidth' => 'w-full',
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
        return array_merge($this->formViewsData($request), []);
    }

    /**
     * Get the resource actions.
     *
     * @return array<int, ResourceAction>
     */
    protected static function resourceActions(): array
    {
        return [
            new ArchiveContextQuestions(),
            new RestoreContextQuestions(),
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
