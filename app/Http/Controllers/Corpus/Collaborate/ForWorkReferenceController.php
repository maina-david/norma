<?php

namespace App\Http\Controllers\Corpus\Collaborate;

use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

class ForWorkReferenceController extends CollaborateController
{
    /** @var string */
    protected string $sortBy = 'id';

    /** @var bool */
    protected bool $withCreate = false;

    /** @var bool */
    protected bool $withDelete = false;

    /** @var bool */
    protected bool $withShow = false;

    /** @var bool */
    protected bool $withUpdate = false;

    /** @var bool */
    protected bool $sortable = false;

    /** @var bool */
    protected bool $searchable = true;

    protected Work $work;

    /**
     * {@inheritDoc}
     */
    protected function authoriseAction(string $action, mixed $arguments = []): void
    {
        $this->authorize('collaborate.corpus.reference.versions.' . $action);
    }

    /**
     * {@inheritDoc}
     */
    protected function baseQuery(Request $request): Builder
    {
        /** @var Route $route */
        $route = $request->route();

        $workId = $route->parameter('work');
        /** @var Work */
        $work = Work::findOrFail($workId);
        $this->work = $work;

        return $work->references()
            ->with(['refPlainText'])
            ->withCount(['normas'])
            ->getQuery();
    }

    protected function preFetch(Builder $builder): void
    {
        $builder->reorder();
        $hasDoc = $this->work->docs()->exists();

        $builder->when($hasDoc, fn ($query) => $query->orderBy('position'))
            ->when(!$hasDoc, fn ($query) => $query->orderBy('volume')->orderBy('start'));
    }

    /**
     * {@inheritDoc}
     */
    protected static function resource(): string
    {
        return Reference::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.corpus.works.references';
    }

    /**
     * @codeCoverageIgnore
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return '';
    }

    /**
     * {@inheritDoc}
     */
    protected static function indexColumns(): array
    {
        return [
            'title' => fn ($row) => self::renderPartial($row, 'for-work.title-column'),
            'streams' => fn ($row) => self::renderPartial($row, 'for-work.normas-count-column'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function indexViewData(Request $request): array
    {
        return [
            'headings' => false,
            'title' => $this->work->title,
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
            'work' => $request->route('work'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected static function turboTarget(Request $request): ?string
    {
        /** @var int $work */
        $work = $request->route('work');

        return "references_corpus_work_{$work}";
    }
}
