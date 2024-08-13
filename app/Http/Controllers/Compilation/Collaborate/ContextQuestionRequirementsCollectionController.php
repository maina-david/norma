<?php

namespace App\Http\Controllers\Compilation\Collaborate;

use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Requests\Compilation\ContextQuestionRequirementsCollectionRequest;
use App\Models\Compilation\ContextQuestion;
use App\Models\Compilation\Pivots\ContextQuestionRequirementsCollection;
use App\Models\Compilation\RequirementsCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ContextQuestionRequirementsCollectionController extends CollaborateController
{
    protected bool $searchable = false;
    protected bool $withShow = false;
    protected bool $withUpdate = false;
    protected bool $withCreate = true;

    /**
     * {@inheritDoc}
     */
    protected static function forResource(): ?string
    {
        return ContextQuestion::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function pivotRelationName(): string
    {
        return 'requirementsCollections';
    }

    protected static function pivotClassName(): ?string
    {
        return ContextQuestionRequirementsCollection::class;
    }

    protected static function resource(): string
    {
        return RequirementsCollection::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceFormRequest(): string
    {
        return ContextQuestionRequirementsCollectionRequest::class;
    }

    protected function permissionPrefix(): string
    {
        return 'collaborate.compilation.context-question.requirements-collection';
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
        /** @var ContextQuestion */
        $contextQuestion = $this->getForResource();

        return $contextQuestion->requirementsCollections()
            ->with('ancestorsWithSelf')
            ->getQuery();
    }

    /**
     * {@inheritDoc}
     */
    protected static function indexColumns(): array
    {
        return [
            'title' => fn ($row) => $row->ancestorsWithSelf->implode('title', ' > '),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.compilation.context-questions.requirements-collections';
    }

    /**
     * {@inheritDoc}
     */
    protected function resourceRouteParams(): array
    {
        /** @var Request $request */
        $request = request();

        return [
            'context_question' => $request->route('context_question'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function indexViewData(Request $request): array
    {
        return [
            'headings' => false,
            'contextQuestion' => $this->getForResource(),
            'view' => 'pages.compilation.collaborate.context-question.for-question-index',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function createViewData(Request $request): array
    {
        return [
            'contextQuestion' => $this->getForResource(),
            'langFile' => 'compilation.context_question.requirements_collection',
            'formWidth' => 'w-full',
        ];
    }
}
