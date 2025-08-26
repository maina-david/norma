<?php

namespace App\Http\Controllers\Traits;

use App\Enums\Corpus\ReferenceType;
use App\Models\Auth\User;
use App\Models\Corpus\Reference;
use App\Models\Corpus\WorkExpression;
use App\Models\Workflows\Note;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

trait UsesAnnotationPage
{
    /** @var int */
    protected int $perPage = 50;

    /**
     * @param Collection<string, string> $meta
     *
     * @return array<string|int, mixed>
     */
    protected function getMetaValues(Collection $meta): array
    {
        $metaValues = $meta->values()->toArray();

        if (in_array('assessmentItems', $metaValues)) {
            $metaValues = array_filter($metaValues, fn ($item) => $item !== 'assessmentItems');
            $metaValues['assessmentItems'] = fn ($query) => $query->notUserGenerated();
        }

        return $metaValues;
    }

    /**
     * Get the base reference query.
     *
     * @param WorkExpression $expression
     *
     * @return Builder
     */
    protected function baseReferenceQuery(WorkExpression $expression): Builder
    {
        /** @var Builder */
        return Reference::where('work_id', $expression->work_id)
            ->whereIn('type', [
                ReferenceType::work()->value,
                ReferenceType::citation()->value,
            ])
            ->orderBy('volume')
            ->orderBy('start');
    }

    /**
     * Check if the work has notes.
     *
     * @param WorkExpression $expression
     *
     * @return bool
     */
    protected function workHasNotes(WorkExpression $expression): bool
    {
        return Note::where(function ($query) use ($expression) {
            $query->where('notable_id', $expression->work_id)
                ->where('notable_type', $expression->work->getMorphClass());
        })
            ->orWhere(function ($query) use ($expression) {
                $query->where('notable_id', $expression->id)
                    ->where('notable_type', $expression->getMorphClass());
            })
            ->exists();
    }

    /**
     * Get the meta fields required to render the reference-meta.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    protected function referenceMetaFields(Request $request): array
    {
        $preview = false;

        if (method_exists($this, 'isPreviewing')) {
            /** @var WorkExpression|int $expression */
            $expression = $request->route('expression');
            $preview = $this->isPreviewing($expression instanceof WorkExpression ? $expression->id : $expression);
        }

        $meta = $this->getVisibleMeta();

        /** @var User $user */
        $user = $request->user();

        $permissions = $preview ? [] : [
            'assessments' => [
                'attach' => $user->can('collaborate.corpus.work-expression.attach-assessment-items'),
                'detach' => $user->can('collaborate.corpus.work-expression.detach-assessment-items'),
            ],
            'topics' => [
                'attach' => $user->can('collaborate.corpus.work-expression.attach-categories'),
                'detach' => $user->can('collaborate.corpus.work-expression.detach-categories'),
            ],
            'context' => [
                'attach' => $user->can('collaborate.corpus.work-expression.attach-context-questions'),
                'detach' => $user->can('collaborate.corpus.work-expression.detach-context-questions'),
            ],
            'domains' => [
                'attach' => $user->can('collaborate.corpus.work-expression.attach-legal-domains'),
                'detach' => $user->can('collaborate.corpus.work-expression.detach-legal-domains'),
            ],
            'jurisdictions' => [
                'attach' => $user->can('collaborate.corpus.work-expression.attach-locations'),
                'detach' => $user->can('collaborate.corpus.work-expression.detach-locations'),
            ],
            'tags' => [
                'attach' => $user->can('collaborate.corpus.work-expression.attach-tags'),
                'detach' => $user->can('collaborate.corpus.work-expression.detach-tags'),
            ],
        ];

        $metaIcons = [
            'assessments' => 'check',
            'topics' => 'hashtag',
            'context' => 'question',
            'domains' => 'scale-balanced',
            'jurisdictions' => 'location-dot',
            'tags' => 'tags',
        ];

        $metaLabels = [
            'assessments' => 'description',
            'topics' => 'display_label',
            'context' => 'question',
            'domains' => 'title',
            'jurisdictions' => 'title',
            'tags' => 'title',
        ];

        $metaClasses = [
            'assessments' => 'text-purple-600 bg-purple-100',
            'topics' => 'text-white',
            'context' => 'text-blue-600 bg-blue-100',
            'domains' => 'text-green-600 bg-green-100',
            'jurisdictions' => 'text-red-600 bg-red-100',
            'tags' => 'text-norma-gray-600 bg-norma-gray-100',
        ];

        $metaDrafts = [
            'assessments' => 'assessmentItemDrafts',
            'topics' => 'categoryDrafts',
            'context' => 'contextQuestionDrafts',
            'domains' => 'legalDomainDrafts',
            'jurisdictions' => 'locationDrafts',
            'tags' => 'tagDrafts',
        ];
        $metaDrafts = collect(array_intersect_key($metaDrafts, $meta->toArray()));

        return [
            'meta' => $meta,
            'metaClasses' => $metaClasses,
            'metaIcons' => $metaIcons,
            'metaLabels' => $metaLabels,
            'metaDrafts' => $metaDrafts,
            'permissions' => $permissions,
        ];
    }
}
