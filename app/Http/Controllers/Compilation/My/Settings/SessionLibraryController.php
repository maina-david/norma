<?php

namespace App\Http\Controllers\Compilation\My\Settings;

use App\Http\Controllers\Controller;
use App\Models\Compilation\Library;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;

class SessionLibraryController extends Controller
{
    /**
     * Get the libraries that are currently in the session.
     *
     * @param string $key
     *
     * @return array<array-key, int>
     */
    public function getLibraries(string $key): array
    {
        /** @var array<array-key, int> */
        return Session::get('libraries_loaded_' . $key, []);
    }

    public function loadedLibraries(string $key): Response
    {
        $libraryIds = $this->getLibraries($key);
        $baseQuery = Library::whereKey($libraryIds);

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.compilation.my.library.load-libraries',
            'target' => 'session-loaded-libraries',
            'baseQuery' => $baseQuery,
            'key' => $key,
        ]);

        return turboStreamResponse($view);
    }

    public function addLibrary(Request $request, string $key): RedirectResponse
    {
        $libraryIds = $this->getLibraries($key);
        $id = $request->input('library_id');
        if (!in_array($id, $libraryIds)) {
            $libraryIds[] = $id;
        }

        Session::put('libraries_loaded_' . $key, $libraryIds);

        return redirect()->route('my.settings.compilation.libraries.session', ['key' => $key]);
    }

    /**
     * @return RedirectResponse
     */
    public function clearLibraries(string $key): RedirectResponse
    {
        Session::forget('libraries_loaded_' . $key);

        return $key === 'compilation'
            ? redirect()->route('my.settings.compilation.manual-compilation')
            // @codeCoverageIgnoreStart
            : redirect()->route('my.settings.compilation.libraries.session', ['key' => $key]);
        // @codeCoverageIgnoreEnd
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param string                   $key
     *
     * @return RedirectResponse
     */
    public function addByReferences(Request $request, string $key): RedirectResponse
    {
        $referenceIds = $request->input('references', []);
        $addLibraryIds = Library::whereRelation('references', fn ($q) => $q->whereKey($referenceIds))->pluck('id')->all();

        $libraryIds = Session::get('libraries_loaded_' . $key, []);
        $libraryIds = array_unique(array_merge($libraryIds, $addLibraryIds));

        Session::put('libraries_loaded_' . $key, $libraryIds);

        return $key === 'compilation'
            ? redirect()->route('my.settings.compilation.manual-compilation')
            // @codeCoverageIgnoreStart
            : redirect()->route('my.settings.compilation.libraries.session', ['key' => $key]);
        // @codeCoverageIgnoreEnd
    }
}
