<?php

namespace App\Http\Controllers\Compilation\Collaborate;

use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Requests\Compilation\ContextQuestionDescriptionRequest;
use App\Models\Compilation\ContextQuestion;
use App\Models\Compilation\ContextQuestionDescription;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ForContextQuestionContextQuestionDescriptionController extends CollaborateController
{
    protected bool $searchable = false;
    protected bool $withShow = false;

    /**
     * {@inheritDoc}
     */
    protected static function resource(): string
    {
        return ContextQuestionDescription::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceFormRequest(): string
    {
        return ContextQuestionDescriptionRequest::class;
    }

    protected static function forResource(): ?string
    {
        return ContextQuestion::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.compilation.context-questions.context-question-descriptions';
    }

    /**
     * {@inheritDoc}
     */
    protected static function indexColumns(): array
    {
        return [
            'location' => fn ($row) => $row->location?->title,
            'description' => fn ($row) => $row->description,
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
    protected function editViewData(Request $request): array
    {
        return [
            'contextQuestion' => $this->getForResource(),
            'formWidth' => 'w-full',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function createViewData(Request $request): array
    {
        return [
            'contextQuestion' => $this->getForResource(),
            'formWidth' => 'w-full',
        ];
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
     * Generate a new query builder instance for the resource.
     *
     * @param Request $request
     *
     * @return Builder
     */
    protected function baseQuery(Request $request): Builder
    {
        $contextQuestion = $this->getForResource();

        return $contextQuestion->descriptions()->with(['location'])->getQuery();
    }
}
