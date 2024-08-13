<?php

namespace App\Http\Controllers\Workflows\Collaborate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workflows\NoteRequest;
use App\Models\Auth\User;
use App\Models\Corpus\Work;
use App\Models\Corpus\WorkExpression;
use App\Models\Workflows\Note;
use HotwiredLaravel\TurboLaravel\Http\MultiplePendingTurboStreamResponse;
use HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    /**
     * Get the notes for the given work.
     *
     * @param Work                $work
     * @param WorkExpression|null $expression
     *
     * @return MultiplePendingTurboStreamResponse
     */
    public function index(Work $work, ?WorkExpression $expression = null): MultiplePendingTurboStreamResponse
    {
        $this->abortNotTurbo();
        $pageName = $expression ? 'expression' : 'work';

        $notes = Note::with(['collaborator']);
        $notes->when($expression, function ($builder) use ($expression) {
            $builder->where('notable_type', (new WorkExpression())->getMorphClass())
                ->where('notable_id', $expression->id ?? null);
        });
        $notes->when(!$expression, function ($builder) use ($work) {
            $builder->where('notable_type', (new Work())->getMorphClass())
                ->where('notable_id', $work->id);
        });
        $notes = $notes
            ->orderBy('id', 'desc')
            ->paginate(30, pageName: "{$pageName}-note-page");

        return multipleTurboStreamResponse([
            singleTurboStreamResponse("{$pageName}-notes")
                ->view('pages.corpus.collaborate.annotations.partials.notes', [
                    'notes' => $notes,
                    'routeParams' => [
                        'expression' => $expression,
                        'work' => $work,
                    ],
                ]),
            singleTurboStreamResponse('has_note_indicator', 'replace')
                ->view('pages.corpus.collaborate.annotations.partials.has-note-metadata-toggle', [
                    'hasNotes' => $notes->isNotEmpty(),
                ]),
        ]);
    }

    /**
     * Redirect to the index page.
     *
     * @param Note $note
     *
     * @return RedirectResponse
     */
    protected function redirectToIndex(Note $note): RedirectResponse
    {
        $note->load(['notable']);

        $notable = $note->notable;

        if ($notable instanceof WorkExpression) {
            return redirect()->route('collaborate.notes.index', [
                'work' => $notable->work_id,
                'expression' => $notable->id,
            ]);
        }

        /** @var Work $notable */
        return redirect()->route('collaborate.notes.index', ['work' => $notable->id]);
    }

    /**
     * Create the resource.
     *
     * @param NoteRequest         $request
     * @param Work                $work
     * @param WorkExpression|null $expression
     *
     * @return RedirectResponse
     */
    public function store(NoteRequest $request, Work $work, ?WorkExpression $expression = null): RedirectResponse
    {
        $this->abortNotTurbo();

        /** @var User $user */
        $user = Auth::user();

        Note::create([
            'author_id' => $user->id,
            'notable_id' => $expression ? $expression->id : $work->id,
            'notable_type' => $expression ? $expression->getMorphClass() : $work->getMorphClass(),
            'content' => $request->get('note'),
        ]);

        $this->notifySuccessfulUpdate();

        return redirect()->route('collaborate.notes.index', ['work' => $work->id, 'expression' => $expression->id ?? null]);
    }

    /**
     * Edit the resource.
     *
     * @param Request $request
     * @param Note    $note
     *
     * @return PendingTurboStreamResponse
     */
    public function edit(Request $request, Note $note): PendingTurboStreamResponse
    {
        $this->abortNotTurbo();

        return singleTurboStreamResponse($request->header('turbo-frame', ''))
            ->view('pages.corpus.collaborate.annotations.partials.notes', [
                'resource' => $note,
                'notes' => new LengthAwarePaginator([], 0, 1),
                'routeParams' => ['note' => $note->id],
            ]);
    }

    /**
     * Update the resource.
     *
     * @param NoteRequest $request
     * @param Note        $note
     *
     * @return RedirectResponse
     */
    public function update(NoteRequest $request, Note $note): RedirectResponse
    {
        $note->update(['content' => $request->get('note')]);
        $note->load(['notable']);

        $this->notifySuccessfulUpdate();

        return $this->redirectToIndex($note);
    }

    /**
     * Delete the resource.
     *
     * @param Note $note
     *
     * @return RedirectResponse
     */
    public function destroy(Note $note): RedirectResponse
    {
        $note->delete();

        return $this->redirectToIndex($note);
    }
}
