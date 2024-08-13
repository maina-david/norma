<?php

namespace App\Http\Controllers\Corpus\Collaborate;

use App\Enums\Corpus\ReferenceStatus;
use App\Enums\Corpus\ReferenceType;
use App\Http\Controllers\Traits\UsesAnnotationPage;
use App\Http\Requests\Corpus\ReferenceContentRequest;
use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceContent;
use App\Models\Corpus\ReferenceText;
use App\Models\Corpus\WorkExpression;
use App\Models\Workflows\Task;
use HotwiredLaravel\TurboLaravel\Http\MultiplePendingTurboStreamResponse;
use HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReferenceContentController extends WorkflowTaskController
{
    use UsesAnnotationPage;

    /**
     * Get the reference editing view.
     *
     * @param Request        $request
     * @param WorkExpression $expression
     * @param Task|null      $task
     *
     * @return View
     */
    public function index(Request $request, WorkExpression $expression, ?Task $task = null): View
    {
        $this->removePreviewing($expression->id);
        $this->switchTask($task);
        $this->setVisibleMeta([]);
        $this->setReferenceFilters([]);

        /** @var View */
        return view('pages.corpus.collaborate.reference-content.index', [
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
            ->whereIn('type', [
                ReferenceType::work()->value,
                ReferenceType::citation()->value,
            ])
            ->with([
                'refSelector:reference_id,selectors',
                'refTitleText:reference_id,text',
                'refPlainText:reference_id,plain_text',
                'refRequirement:reference_id',
                'requirementDraft:reference_id',
            ])
            ->select([
                'id', 'work_id', 'volume', 'level', 'type', 'status',
            ])
            ->orderBy('volume')
            ->orderBy('start');
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

        $references = $this->baseQuery($expression)->paginate($this->perPage);

        return multipleTurboStreamResponse([
            singleTurboStreamResponse('work_expression_references', 'update')
                ->view('pages.corpus.collaborate.reference-content.partials.references', [
                    'expression' => $expression,
                    'references' => $references,
                    'task' => $task,
                    'activeReference' => null,
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
        $previous = session('collaborate_active_creation_reference');
        session()->put('collaborate_active_creation_reference', $reference->id);

        $reference->load(['refSelector:reference_id,selectors']);

        $response = [
            singleTurboStreamResponse("reference_{$reference->id}", 'replace')
                ->view('pages.corpus.collaborate.reference-content.partials.reference-card', [
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

        $reference->load(['refSelector:reference_id,selectors']);

        return singleTurboStreamResponse("reference_{$reference->id}", 'replace')
            ->view('pages.corpus.collaborate.reference-content.partials.reference-card', [
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
            ->view('pages.corpus.collaborate.reference-content.partials.reference-text-content', [
                'expression' => $expression,
                'reference' => $reference->load(['htmlContent:reference_id,cached_content']),
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

        $title = trim($request->get('title'));
        ReferenceText::updateOrCreate(['reference_id' => $reference->id], [
            'reference_id' => $reference->id,
            'plain_text' => $title,
        ]);

        ReferenceContent::updateOrCreate(['reference_id' => $reference->id], [
            'reference_id' => $reference->id,
            'cached_content' => $request->get('content'),
        ]);

        $this->notifySuccessfulUpdate();

        return redirect()->route('collaborate.work-expressions.creation.references.activate', [
            'reference' => $reference->id,
            'expression' => $expression->id,
        ]);
    }

    /**
     * Insert the reference below the given one.
     *
     * @param Request        $request
     * @param WorkExpression $expression
     * @param Reference      $reference
     *
     * @return PendingTurboStreamResponse
     */
    public function insertBelow(Request $request, WorkExpression $expression, Reference $reference): PendingTurboStreamResponse
    {
        $this->abortNotTurbo();

        Reference::where('work_id', $reference->work_id)
            ->where('volume', $reference->volume)
            ->where('start', '>', $reference->start ?? 0)
            ->update(['start' => DB::raw('start + 1')]);

        $created = Reference::create([
            'parent_id' => $reference->parent_id,
            'work_id' => $reference->work_id,
            'status' => ReferenceStatus::pending()->value,
            'type' => ReferenceType::citation()->value,
            'volume' => $reference->volume,
            'level' => $reference->level,
            'start' => ($reference->start ?? 0) + 1,
        ]);

        return singleTurboStreamResponse("reference_{$reference->id}_after", 'replace')
            ->view('pages.corpus.collaborate.reference-content.partials.inserted-reference-card', [
                'active' => false,
                'expression' => $expression,
                'insertedBefore' => $reference,
                'reference' => $created,
            ]);
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

        $reference->delete();

        return singleTurboStreamResponse("reference_{$reference->id}", 'remove');
    }

    /**
     * Indent the reference.
     *
     * @param WorkExpression $expression
     * @param string         $reference
     *
     * @return MultiplePendingTurboStreamResponse
     */
    public function indent(WorkExpression $expression, string $reference): MultiplePendingTurboStreamResponse
    {
        $references = $this->getBulkReferences($reference);

        foreach ($references as $ref) {
            /** @var Reference|null $parent */
            $parent = Reference::where('id', '!=', $ref->id)
                ->where('work_id', $ref->work_id)
                ->where('level', '<', $ref->level + 1)
                ->where('start', '<', $ref->start)
                ->orderBy('volume', 'desc')
                ->orderBy('start', 'desc')
                ->first();

            $ref->update([
                'parent_id' => $parent->id ?? null,
                'level' => $ref->level + 1,
            ]);
        }

        $this->notifySuccessfulUpdate();

        return $this->indentOutdentResponse($expression, $references);
    }

    /**
     * Outdent the reference.
     *
     * @param WorkExpression $expression
     * @param string         $reference
     *
     * @return MultiplePendingTurboStreamResponse
     */
    public function outdent(WorkExpression $expression, string $reference): MultiplePendingTurboStreamResponse
    {
        $references = $this->getBulkReferences($reference);

        foreach ($references as $ref) {
            if ($ref->level > 1) {
                /** @var Reference|null $parent */
                $parent = Reference::where('id', '!=', $ref->id)
                    ->where('work_id', $ref->work_id)
                    ->where('level', '<', $ref->level - 1)
                    ->where('start', '<', $ref->start)
                    ->orderBy('volume', 'desc')
                    ->orderBy('start', 'desc')
                    ->first();

                $ref->update([
                    'parent_id' => $parent->id ?? null,
                    'level' => $ref->level - 1,
                ]);
            }
        }

        $this->notifySuccessfulUpdate();

        return $this->indentOutdentResponse($expression, $references);
    }

    /**
     * @param string $reference
     *
     * @return \Illuminate\Support\Collection<int, Reference>
     */
    protected function getBulkReferences(string $reference): Collection
    {
        $references = $reference === 'bulk' ? collect(explode(',', request('references', '')))->filter()->all() : [$reference];

        return Reference::whereIn('id', $references)
            ->whereIn('type', [
                ReferenceType::work()->value,
                ReferenceType::citation()->value,
            ])
            ->with([
                'refSelector:reference_id,selectors',
                'refTitleText:reference_id,text',
                'refPlainText:reference_id,plain_text',
                'refRequirement:reference_id',
                'requirementDraft:reference_id',
            ])
            ->get(['id', 'work_id', 'volume', 'level', 'type', 'status', 'start']);
    }

    /**
     * @param \App\Models\Corpus\WorkExpression              $expression
     * @param \Illuminate\Support\Collection<int, Reference> $references
     *
     * @return \HotwiredLaravel\TurboLaravel\Http\MultiplePendingTurboStreamResponse
     */
    protected function indentOutdentResponse(WorkExpression $expression, Collection $references): MultiplePendingTurboStreamResponse
    {
        $mapped = $references->map(fn ($ref) => $this->deactivate($expression, $ref))->all();

        return multipleTurboStreamResponse($mapped);
    }
}
