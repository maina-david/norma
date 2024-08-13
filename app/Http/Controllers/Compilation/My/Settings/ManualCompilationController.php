<?php

namespace App\Http\Controllers\Compilation\My\Settings;

use App\Http\Controllers\Controller;
use App\Models\Compilation\Library;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Stores\Compilation\LibraryReferenceStore;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;

class ManualCompilationController extends Controller
{
    public function __construct(protected LibraryReferenceStore $libraryReferenceStore)
    {
    }

    /**
     * @param Request $request
     *
     * @return View
     */
    public function compilation(Request $request): View
    {
        /** @var array<int> */
        $workIds = Session::get('manual_compilation_works', []);
        /** @var array<int> */
        $libraryIds = Session::get('libraries_loaded_compilation', []);

        /** @var View */
        return view('pages.compilation.my.library.settings.manual-compilation', [
            'works' => Work::whereKey($workIds)
                ->with([
                    'references' => fn ($q) => $q->compilable(),
                    'references.htmlContent' => fn ($q) => $q->select(['reference_id', 'cached_content']),
                    'references.refPlainText' => fn ($q) => $q->select(['reference_id', 'plain_text']),
                    'references.citation' => fn ($q) => $q->select(['id', 'heading', 'number']),
                ])
                ->get(),
            'libraries' => Library::whereKey($libraryIds)->get(['id']),
        ]);
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function addWork(Request $request): RedirectResponse
    {
        /** @var array<int> */
        $workIds = Session::get('manual_compilation_works', []);
        $workId = $request->input('work_id');
        $includeChildren = (bool) $request->input('include_children');

        if (!in_array($workId, $workIds)) {
            $workIds[] = $workId;
        }

        if ($includeChildren) {
            /** @var Work */
            $work = Work::find($workId);
            foreach ($work->children()->active()->get() as $child) {
                if (!in_array($child->id, $workIds)) {
                    $workIds[] = $child->id;
                }
            }
        }

        Session::put('manual_compilation_works', $workIds);

        return redirect()->route('my.settings.compilation.manual-compilation');
    }

    /**
     * @return RedirectResponse
     */
    public function clearWorks(): RedirectResponse
    {
        Session::forget('manual_compilation_works');

        return redirect()->route('my.settings.compilation.manual-compilation');
    }

    public function compileReference(Reference $reference): Response
    {
        /** @var array<int> */
        $libraryIds = Session::get('libraries_loaded_compilation', []);

        /** @var Collection<Library> */
        $libraries = Library::whereKey($libraryIds)->get(['id']);
        /** @var Collection<Reference> */
        $references = $reference->newCollection([$reference]);
        foreach ($libraries as $library) {
            $this->libraryReferenceStore->attachReferences($library, $references);
        }

        return $this->respondWithEmpty('manual-compilation-reference-' . $reference->id);
    }

    public function decompileReference(Reference $reference): Response
    {
        /** @var array<int> */
        $libraryIds = Session::get('libraries_loaded_compilation', []);

        /** @var Collection<Library> */
        $libraries = Library::whereKey($libraryIds)->get(['id']);
        /** @var Collection<Reference> */
        $references = $reference->newCollection([$reference]);
        foreach ($libraries as $library) {
            $this->libraryReferenceStore->detachReferences($library, $references);
        }

        return $this->respondWithEmpty('manual-compilation-reference-remove-' . $reference->id);
    }

    public function compileWork(Work $work): Response
    {
        /** @var array<int> */
        $libraryIds = Session::get('libraries_loaded_compilation', []);

        /** @var Collection<Library> */
        $libraries = Library::whereKey($libraryIds)->get(['id']);
        foreach ($libraries as $library) {
            $this->libraryReferenceStore->attachReferences($library, $work->references()
                ->typeCitation()
                ->compilable()
                ->get(['id']));
        }

        return $this->respondWithEmpty('manual-compilation-work-' . $work->id);
    }

    public function decompileWork(Work $work): Response
    {
        /** @var array<int> */
        $libraryIds = Session::get('libraries_loaded_compilation', []);

        /** @var Collection<Library> */
        $libraries = Library::whereKey($libraryIds)->get(['id']);
        foreach ($libraries as $library) {
            $this->libraryReferenceStore->detachReferences($library, $work->references()
                ->typeCitation()
                ->compilable()
                ->get(['id']));
        }

        return $this->respondWithEmpty('manual-compilation-work-remove-' . $work->id);
    }

    public function compileAllWorks(Work $work): Response
    {
        /** @var array<int> */
        $libraryIds = Session::get('libraries_loaded_compilation', []);
        /** @var array<int> */
        $workIds = Session::get('manual_compilation_works', []);

        /** @var Collection<Library> */
        $libraries = Library::whereKey($libraryIds)->get(['id']);
        /** @var Collection<Work> */
        $works = Work::whereKey($workIds)->get(['id']);
        foreach ($libraries as $library) {
            foreach ($works as $work) {
                $this->attachWork($library, $work);
            }
        }

        return $this->respondWithEmpty('manual-compilation-all-works');
    }

    public function decompileAllWorks(Work $work): Response
    {
        /** @var array<int> */
        $libraryIds = Session::get('libraries_loaded_compilation', []);
        /** @var array<int> */
        $workIds = Session::get('manual_compilation_works', []);

        /** @var Collection<Library> */
        $libraries = Library::whereKey($libraryIds)->get(['id']);
        /** @var Collection<Work> */
        $works = Work::whereKey($workIds)->get(['id']);
        foreach ($libraries as $library) {
            foreach ($works as $work) {
                $this->detachWork($library, $work);
            }
        }

        return $this->respondWithEmpty('manual-compilation-all-works-remove');
    }

    private function attachWork(Library $library, Work $work): void
    {
        $this->libraryReferenceStore->attachReferences($library, $work->references()
            ->typeCitation()
            ->compilable()
            ->get(['id']));
    }

    private function detachWork(Library $library, Work $work): void
    {
        $this->libraryReferenceStore->detachReferences($library, $work->references()
            ->typeCitation()
            ->compilable()
            ->get(['id']));
    }

    private function respondWithEmpty(string $target): Response
    {
        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.ui.raw-content',
            'target' => $target,
            'content' => '',
        ]);

        return turboStreamResponse($view);
    }
}
