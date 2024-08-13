<?php

namespace App\Http\Controllers\Traits;

use App\Models\Assess\AssessmentItem;
use App\Models\Compilation\ContextQuestion;
use App\Models\Corpus\Work;
use App\Models\Corpus\WorkExpression;
use App\Models\Geonames\Location;
use App\Models\Ontology\Category;
use App\Models\Ontology\LegalDomain;
use App\Models\Ontology\Tag;
use App\Models\Workflows\Note;
use App\Models\Workflows\Task;
use Illuminate\Http\Request;

trait AnnotatesWorks
{
    /**
     * Reset the applied meta and filters.
     *
     * @return void
     */
    protected function resetMetaAndFilters(): void
    {
        $this->setVisibleMeta([]);
        $this->setReferenceFilters([]);
    }

    /**
     * Switch the expression and task and authorise the switch.
     *
     * @param string                            $permission
     * @param \App\Models\Corpus\WorkExpression $expression
     * @param \App\Models\Workflows\Task|null   $task
     *
     * @return void
     */
    protected function switchAndAuthorise(string $permission, WorkExpression $expression, ?Task $task = null): void
    {
        $this->authorize($permission, [$expression, $task]);
        $this->removePreviewing($expression->id);
        $this->switchTask($task);
        $this->resetMetaAndFilters();
    }

    /**
     * Get the query and map of the filterables.
     *
     * @return array<string, mixed>
     */
    protected function getFiltersQueryAndMap(): array
    {
        return [
            'assessmentItems' => [
                'map' => fn (AssessmentItem $item) => ['id' => $item->id, 'title' => $item->description],
                'query' => AssessmentItem::select([
                    'id', 'component_1', 'component_2', 'component_3', 'component_4', 'component_5', 'component_6',
                    'component_7', 'component_8',
                ]),
            ],
            'topics' => [
                'map' => fn (Category $item) => ['id' => $item->id, 'title' => $item->display_label],
                'query' => Category::select([
                    'id', 'display_label',
                ]),
            ],
            'questions' => [
                'map' => fn (ContextQuestion $item) => ['id' => $item->id, 'title' => $item->question],
                'query' => ContextQuestion::select([
                    'id', 'prefix', 'predicate', 'pre_object', 'object', 'post_object',
                ]),
            ],
            'legalDomains' => [
                'map' => fn (LegalDomain $item) => ['id' => $item->id, 'title' => $item->title],
                'query' => LegalDomain::select(['id', 'title']),
            ],
            'locations' => [
                'map' => fn (Location $item) => ['id' => $item->id, 'title' => $item->title],
                'query' => Location::select(['id', 'title']),
            ],
            'tags' => [
                'map' => fn (Tag $item) => ['id' => $item->id, 'title' => $item->title],
                'query' => Tag::select(['id', 'title']),
            ],
        ];
    }

    /**
     * Get the filters that have been applied.
     *
     * @param \Illuminate\Http\Request $request
     * @param array<string, mixed>     $filters
     *
     * @return array<string, mixed>
     */
    protected function getAppliedFilters(Request $request, array $filters): array
    {
        // @phpstan-ignore-next-line
        return collect($request->query())
            ->filter(fn ($item, $key) => array_key_exists($key, $filters))
            ->map(function ($items, $key) use ($filters) {
                return $filters[$key]['query']->whereKey($items)
                    ->get()
                    ->map(fn ($item) => $filters[$key]['map']($item))
                    ->all();
            })
            ->all();
    }

    /**
     * Get the page meta details.
     *
     * @param \App\Models\Corpus\WorkExpression $expression
     * @param array<string, mixed>              $filters
     *
     * @return array<string, mixed>
     */
    protected function getPageMeta(WorkExpression $expression, array $filters): array
    {
        return collect($filters)
            ->map(function ($item) use ($expression) {
                $items = $item['query']
                    ->clone()
                    ->whereRelation('references', 'work_id', $expression->work_id)
                    ->get()
                    ->map(fn ($fetched) => $item['map']($fetched))
                    ->toBase();

                $drafts = $item['query']
                    ->whereRelation('drafts.reference', 'work_id', $expression->work_id)
                    ->get()
                    ->map(fn ($fetched) => $item['map']($fetched))
                    ->toBase();

                return $items->merge($drafts);
            })
            ->all();
    }

    /**
     * Check if the work has notes.
     *
     * @param \App\Models\Corpus\WorkExpression $expression
     *
     * @return bool
     */
    protected function workHasNotes(WorkExpression $expression): bool
    {
        return Note::where(function ($query) use ($expression) {
            $query->where('notable_id', $expression->id)
                ->where('notable_type', (new WorkExpression())->getMorphClass());
        })
            ->orWhere(function ($query) use ($expression) {
                $query->where('notable_id', $expression->work_id)->where('notable_type', (new Work())->getMorphClass());
            })
            ->exists();
    }
}
