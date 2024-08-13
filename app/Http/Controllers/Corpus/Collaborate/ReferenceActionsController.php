<?php

namespace App\Http\Controllers\Corpus\Collaborate;

use App\Contracts\Http\ResourceAction;
use App\Http\Controllers\Traits\HasResourceActions;
use App\Http\ResourceActions\Corpus\ApplyDraftAssessmentItems;
use App\Http\ResourceActions\Corpus\ApplyDraftCategories;
use App\Http\ResourceActions\Corpus\ApplyDraftContextQuestions;
use App\Http\ResourceActions\Corpus\ApplyDraftLegalDomains;
use App\Http\ResourceActions\Corpus\ApplyDraftLocations;
use App\Http\ResourceActions\Corpus\ApplyDraftReference;
use App\Http\ResourceActions\Corpus\ApplyDraftRequirement;
use App\Http\ResourceActions\Corpus\ApplyDraftSummaries;
use App\Http\ResourceActions\Corpus\ApplyDraftTags;
use App\Http\ResourceActions\Corpus\ApplyReferenceContentDraft;
use App\Http\ResourceActions\Corpus\AttemptMatchingTocToReference;
use App\Http\ResourceActions\Corpus\CreateDraftReference;
use App\Http\ResourceActions\Corpus\CreateDraftRequirement;
use App\Http\ResourceActions\Corpus\CreateDraftSummaries;
use App\Http\ResourceActions\Corpus\CreateReferenceContentDraft;
use App\Http\ResourceActions\Corpus\DeleteDraftRequirement;
use App\Http\ResourceActions\Corpus\DeleteDraftSummaries;
use App\Http\ResourceActions\Corpus\DeleteReference;
use App\Http\ResourceActions\Corpus\DeleteReferenceContentDraft;
use App\Http\ResourceActions\Corpus\DeleteReferenceExtracts;
use App\Http\ResourceActions\Corpus\DeleteRequirement;
use App\Http\ResourceActions\Corpus\DeleteSummaries;
use App\Http\ResourceActions\Corpus\GenerateReferenceExtracts;
use App\Models\Corpus\Reference;
use App\Models\Corpus\WorkExpression;
use HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse;
use Illuminate\Http\Request;

class ReferenceActionsController extends WorkflowTaskController
{
    use HasResourceActions;

    /**
     * {@inheritDoc}
     */
    protected static function resourceRoute(): string
    {
        // @codeCoverageIgnoreStart
        return 'collaborate.work-expressions.references';
        // @codeCoverageIgnoreEnd
    }

    /**
     * Get the resource actions.
     *
     * @return array<int, ResourceAction>
     */
    protected static function resourceActions(): array
    {
        /** @var Request $request */
        $request = request();
        /** @var WorkExpression $expression */
        $expression = WorkExpression::findOrFail($request->route('expression'));

        return [
            new ApplyDraftAssessmentItems($expression->work_id, $expression->id),
            new ApplyDraftCategories($expression->work_id, $expression->id),
            new ApplyDraftContextQuestions($expression->work_id, $expression->id),
            new ApplyDraftLegalDomains($expression->work_id, $expression->id),
            new ApplyDraftLocations($expression->work_id, $expression->id),
            new ApplyDraftReference($expression->work_id, $expression->id),
            new ApplyDraftRequirement($expression->work_id, $expression->id),
            new ApplyDraftSummaries($expression->work_id, $expression->id),
            new ApplyDraftTags($expression->work_id, $expression->id),
            new AttemptMatchingTocToReference($expression->work_id, $expression->id),
            new CreateDraftReference($expression->work_id, $expression->id),
            new CreateDraftRequirement($expression->work_id, $expression->id),
            new DeleteDraftRequirement($expression->work_id, $expression->id),
            new CreateDraftSummaries($expression->work_id, $expression->id),
            new DeleteDraftSummaries($expression->work_id, $expression->id),
            new DeleteReference($expression->work_id, $expression->id),
            new DeleteRequirement($expression->work_id, $expression->id),
            new DeleteSummaries($expression->work_id, $expression->id),
            new DeleteReferenceExtracts($expression->work_id, $expression->id),
            new GenerateReferenceExtracts($expression->work_id, $expression->id),

            new CreateReferenceContentDraft($expression->work_id, $expression->id),
            new ApplyReferenceContentDraft($expression->work_id, $expression->id),
            new DeleteReferenceContentDraft($expression->work_id, $expression->id),
        ];
    }

    /**
     * Check on the validity of the action and show a check warning.
     *
     * @param Request        $request
     * @param WorkExpression $expression
     *
     * @return PendingTurboStreamResponse
     */
    public function checkAction(Request $request, WorkExpression $expression): PendingTurboStreamResponse
    {
        $suffix = match ($request->get('action')) {
            'apply-requirement-drafts' => Reference::whereKey($this->extractIds($request))
                ->where('work_id', $expression->work_id)
                ->typeCitation()
                ->has('requirementDraft')
                ->doesntHave('contextQuestions')
                ->exists(),
            // @codeCoverageIgnoreStart
            default => false,
            // @codeCoverageIgnoreEnd
        };

        $suffix = $suffix ? '_check_failed' : '';

        return singleTurboStreamResponse($request->get('action'), 'replace')
            ->view('partials.corpus.reference.collaborate.check-bulk-action', [
                'expression' => $expression,
                'labelSuffix' => $request->get('label_suffix'),
                'checkSuffix' => $suffix,
                'action' => $request->get('action'),
            ]);
    }
}
