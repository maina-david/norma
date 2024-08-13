<?php

namespace App\Http\Controllers\Corpus\Collaborate;

use App\Actions\Corpus\Reference\ApplyAllReferenceContentDrafts;
use App\Actions\Corpus\Reference\DeleteNonRequirementReferences;
use App\Actions\Corpus\Reference\InsertNewReferenceBelow;
use App\Actions\Corpus\Reference\InsertNewReferenceFromTocItem;
use App\Actions\Corpus\Reference\RequestReferenceContentUpdate;
use App\Actions\Corpus\Reference\UpdateReferenceFromTocItem;
use App\Actions\Corpus\Reference\UpdateReferencesContentFromDoc;
use App\Enums\Corpus\ReferenceStatus;
use App\Enums\Requirements\RefRequirementChangeStatus;
use App\Http\Requests\Corpus\ReferenceContentRequest;
use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceContentDraft;
use App\Models\Corpus\TocItem;
use App\Models\Corpus\WorkExpression;
use App\Models\Requirements\ReferenceRequirementDraft;
use App\Models\Workflows\Task;
use App\Services\HTMLPurifierService;
use Exception;
use HotwiredLaravel\TurboLaravel\Http\MultiplePendingTurboStreamResponse;
use HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class IdentifyReferenceController extends WorkflowTaskController
{
    public function __construct(protected HTMLPurifierService $htmlPurifier)
    {
    }

    /**
     * Get the reference editing view.
     *
     * @param WorkExpression $expression
     * @param Task|null      $task
     *
     * @return View
     */
    public function index(WorkExpression $expression, ?Task $task = null): View
    {
        $this->removePreviewing($expression->id);
        $this->switchTask($task);
        $this->setVisibleMeta([]);
        $this->setReferenceFilters([]);

        session()->remove('collaborate_active_identify_reference');

        /** @var View */
        return view('pages.corpus.collaborate.identify-references.index', [
            'expression' => $expression,
            'task' => $task,
        ]);
    }

    /**
     * Get the common base query.
     *
     * @param WorkExpression $expression
     *
     * @return Builder
     */
    protected function baseQuery(WorkExpression $expression): Builder
    {
        /** @var Builder */
        return Reference::where('work_id', $expression->work_id)
            ->typeCitation()
            ->with([
                'refPlainText:reference_id,plain_text',
                'contentDraft:reference_id,title,html_content',
                'refRequirement:reference_id',
                'requirementDraft:reference_id',
                'latestTocItem.parent.parent.parent',
            ])
            ->select([
                'id', 'uid', 'work_id', 'type', 'status', 'position',
            ])
            ->orderBy('position');
    }

    /**
     * Get the references for the given work.
     *
     * @param WorkExpression $expression
     *
     * @return MultiplePendingTurboStreamResponse
     */
    public function references(WorkExpression $expression): MultiplePendingTurboStreamResponse
    {
        $this->abortNotTurbo();

        $task = $this->task();

        $references = $this->baseQuery($expression)->paginate(50);
        $activeReferenceId = session()->get('collaborate_active_identify_reference');

        return multipleTurboStreamResponse([
            singleTurboStreamResponse('work_expression_references', 'update')
                ->view('pages.corpus.collaborate.identify-references.partials.references', [
                    'expression' => $expression,
                    'references' => $references,
                    'workReference' => $expression->work->workReference,
                    'task' => $task,
                    'activeReferenceId' => $activeReferenceId,
                ]),
        ]);
    }

    /**
     * Activate the reference.
     *
     * @param WorkExpression $expression
     * @param Reference      $reference
     *
     * @return MultiplePendingTurboStreamResponse
     */
    public function activate(WorkExpression $expression, Reference $reference): MultiplePendingTurboStreamResponse
    {
        $this->abortNotTurbo();

        /** @var string|null $previous */
        $previous = session('collaborate_active_identify_reference');

        $reference->load(['refSelector:reference_id,selectors']);

        $response = [
            singleTurboStreamResponse("reference_{$reference->id}", 'replace')
                ->view('pages.corpus.collaborate.identify-references.partials.reference-card', [
                    'active' => true,
                    'expression' => $expression,
                    'reference' => $reference,
                ]),
        ];

        // @codeCoverageIgnoreStart
        if ($previous && $previous !== $reference->id) {
            /** @var Reference|null $previous */
            $previous = Reference::find($previous);

            if ($previous) {
                $response[] = $this->deactivate($expression, $previous);
            }
        }
        // @codeCoverageIgnoreEnd

        session()->put('collaborate_active_identify_reference', $reference->id);

        return multipleTurboStreamResponse($response);
    }

    /**
     * Deactivate the reference.
     *
     * @param WorkExpression $expression
     * @param Reference      $reference
     *
     * @return PendingTurboStreamResponse
     */
    public function deactivate(WorkExpression $expression, Reference $reference): PendingTurboStreamResponse
    {
        $this->abortNotTurbo();
        session()->remove('collaborate_active_identify_reference');

        return singleTurboStreamResponse("reference_{$reference->id}", 'replace')
            ->view('pages.corpus.collaborate.identify-references.partials.reference-card', [
                'active' => false,
                'expression' => $expression,
                'reference' => $reference,
            ]);
    }

    /**
     * Get the reference content.
     *
     * @param WorkExpression $expression
     * @param Reference      $reference
     *
     * @return PendingTurboStreamResponse
     */
    public function contentStream(WorkExpression $expression, Reference $reference): PendingTurboStreamResponse
    {
        $this->abortNotTurbo();

        return singleTurboStreamResponse("reference_{$reference->id}_content", 'update')
            ->view('pages.corpus.collaborate.identify-references.partials.reference-text-content', [
                'expression' => $expression,
                'reference' => $reference->load([
                    'htmlContent:reference_id,cached_content',
                    'contentDraft:reference_id,title,html_content',
                    'contentVersions.versionContentHtml',
                    'requirementDraft:reference_id',
                    'refRequirement:reference_id',
                ]),
            ]);
    }

    /**
     * Update the reference.
     *
     * @param ReferenceContentRequest $request
     * @param WorkExpression          $expression
     * @param Reference               $reference
     *
     * @return RedirectResponse
     */
    public function update(ReferenceContentRequest $request, WorkExpression $expression, Reference $reference): RedirectResponse
    {
        $this->abortNotTurbo();

        $title = trim(strip_tags($request->get('title')));
        $content = $this->htmlPurifier->cleanSection($request->get('content') ?? '');
        ReferenceContentDraft::updateOrCreate(['reference_id' => $reference->id], [
            'reference_id' => $reference->id,
            'title' => $title,
            'html_content' => $content,
            'author_id' => $request->user()?->id,
        ]);

        if ($request->input('request_requirement')) {
            ReferenceRequirementDraft::updateOrCreate(['reference_id' => $reference->id], [
                'reference_id' => $reference->id,
                'applied_by' => $request->user()?->id,
                'change_status' => RefRequirementChangeStatus::ADD->value,
            ]);
        }

        $this->notifySuccessfulUpdate();

        return redirect()
            ->route('collaborate.work-expressions.identify.references.activate', [
                'reference' => $reference->id,
                'expression' => $expression->id,
            ]);
    }

    /**
     * Insert the reference below the given one.
     *
     * @param WorkExpression $expression
     * @param Reference      $reference
     *
     * @return RedirectResponse
     */
    public function insertBelow(WorkExpression $expression, Reference $reference): RedirectResponse
    {
        $this->abortNotTurbo();

        $newRef = app(InsertNewReferenceBelow::class)->handle($reference);

        session()->put('collaborate_active_identify_reference', $newRef->id);

        return app_redirect_back();
    }

    /**
     * Create a new reference from the given ToCItem.
     *
     * @param WorkExpression $expression
     * @param TocItem        $tocItem
     *
     * @return RedirectResponse
     */
    public function insertFromTocItem(WorkExpression $expression, TocItem $tocItem): RedirectResponse
    {
        $this->abortNotTurbo();

        $refId = session()->get('collaborate_active_identify_reference');

        /** @var Reference|null */
        $reference = $refId ? Reference::find($refId) : null;

        app(InsertNewReferenceFromTocItem::class)
            ->handle($tocItem, $expression->work_id, $reference);

        $back = request('redirect_back') ? app_redirect_back() : null;

        return $back ?? to_route('collaborate.work-expressions.identify.references.index', [
            'expression' => $expression->id,
        ]);
    }

    /**
     * Update the active reference from the given ToCItem.
     *
     * @param WorkExpression $expression
     * @param TocItem        $tocItem
     *
     * @return RedirectResponse
     */
    public function updateFromTocItem(WorkExpression $expression, TocItem $tocItem): RedirectResponse
    {
        $this->abortNotTurbo();

        $refId = session()->get('collaborate_active_identify_reference');

        /** @var Reference|null */
        $reference = $refId ? Reference::find($refId) : null;

        if ($reference && $reference->work_id === $expression->work_id) {
            app(UpdateReferenceFromTocItem::class)
                ->handle($tocItem, $reference, request()->user());
        }

        $back = request('redirect_back') ? app_redirect_back() : null;

        return $back ?? to_route('collaborate.work-expressions.identify.references.index', [
            'expression' => $expression->id,
        ]);
    }

    /**
     * Insert the reference below the given one.
     *
     * @param WorkExpression $expression
     * @param Reference      $reference
     *
     * @return RedirectResponse
     */
    public function moveUp(WorkExpression $expression, Reference $reference): RedirectResponse
    {
        $this->abortNotTurbo();

        /** @var Reference|null */
        $before = Reference::where('work_id', $reference->work_id)
            ->typeCitation()
            ->where('position', '<', $reference->position)
            ->orderBy('position', 'desc')
            ->first();

        if ($before) {
            $newPos = $before->position;
            $before->update(['position' => $reference->position]);
            $reference->update(['position' => $newPos]);
        }

        return app_redirect_back();
    }

    /**
     * Insert the reference below the given one.
     *
     * @param WorkExpression $expression
     * @param Reference      $reference
     *
     * @return RedirectResponse
     */
    public function moveDown(WorkExpression $expression, Reference $reference): RedirectResponse
    {
        $this->abortNotTurbo();

        /** @var Reference|null */
        $after = Reference::where('work_id', $reference->work_id)
            ->typeCitation()
            ->where('position', '>', $reference->position)
            ->first();

        if ($after) {
            $newPos = $after->position;
            $after->update(['position' => $reference->position]);
            $reference->update(['position' => $newPos]);
        }

        return app_redirect_back();
    }

    /**
     * Delete the given reference.
     *
     * @param Reference $reference
     *
     * @return PendingTurboStreamResponse
     */
    public function destroy(Reference $reference): PendingTurboStreamResponse
    {
        $this->abortNotTurbo();

        if (is_null($reference->contentDraft)) {
            // @codeCoverageIgnoreStart
            abort(403);
            // @codeCoverageIgnoreEnd
        }

        $reference->contentDraft->delete();
        if ($reference->status === ReferenceStatus::pending()->value) {
            $reference->delete();
        }

        return singleTurboStreamResponse("reference_{$reference->id}", 'remove');
    }

    /**
     * @param WorkExpression $expression
     *
     * @return RedirectResponse
     */
    public function generateDrafts(WorkExpression $expression): RedirectResponse
    {
        $this->abortNotTurbo();

        /** @var \App\Models\Corpus\Doc $doc */
        $doc = $expression->doc;

        app(UpdateReferencesContentFromDoc::class)->handle($expression->work, $doc, auth()->user());

        $this->notifyGeneralSuccess();

        return redirect()
            ->route('collaborate.work-expressions.identify.references.index', [
                'expression' => $expression->id,
            ]);
    }

    /**
     * @param WorkExpression $expression
     *
     * @return RedirectResponse
     */
    public function applyAllDrafts(WorkExpression $expression): RedirectResponse
    {
        $this->abortNotTurbo();

        app(ApplyAllReferenceContentDrafts::class)
            ->handle($expression->work, auth()->user());

        return app_redirect_back();
    }

    /**
     * @param WorkExpression $expression
     *
     * @throws Exception
     *
     * @return RedirectResponse
     */
    public function deleteNonRequirements(WorkExpression $expression): RedirectResponse
    {
        $this->abortNotTurbo();

        app(DeleteNonRequirementReferences::class)->handle($expression->work);

        return app_redirect_back();
    }

    /**
     * @param WorkExpression $expression
     * @param Reference      $reference
     *
     * @return RedirectResponse
     */
    public function requestUpdate(WorkExpression $expression, Reference $reference): RedirectResponse
    {
        $this->abortNotTurbo();

        app(RequestReferenceContentUpdate::class)->handle($reference, auth()->user());

        return app_redirect_back();
    }

    /**
     * Delete the given reference.
     *
     * @param Reference $reference
     *
     * @return PendingTurboStreamResponse
     */
    public function previewDraft(Reference $reference): PendingTurboStreamResponse
    {
        $this->abortNotTurbo();

        return singleTurboStreamResponse("reference_{$reference->id}_content_draft_preview", 'update')
            ->view('pages.corpus.collaborate.identify-references.partials.preview-content', [
                'title' => $reference->contentDraft?->title,
                'htmlContent' => $reference->contentDraft?->html_content,
            ]);
    }
}
