<?php

namespace App\Http\Controllers\Compilation\My\Settings;

use App\Actions\Compilation\Library\GenericCompilation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Compilation\GenericCompileRequest;
use App\Models\Compilation\Library;
use App\Models\Geonames\Location;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class GenericCompilationController extends Controller
{
    /**
     * @param Library $library
     *
     * @return View
     */
    public function showCompile(Library $library): View
    {
        /** @var View */
        $view = view('pages.compilation.my.library.settings.generic-compilation', [
            'library' => $library,
            'action' => 'compile',
        ]);

        return $view;
    }

    /**
     * @param Library $library
     *
     * @return View
     */
    public function showDecompile(Library $library): View
    {
        /** @var View */
        $view = view('pages.compilation.my.library.settings.generic-compilation', [
            'library' => $library,
            'action' => 'decompile',
        ]);

        return $view;
    }

    /**
     * @param GenericCompileRequest $request
     * @param Library               $library
     *
     * @return RedirectResponse
     */
    public function compile(GenericCompileRequest $request, Library $library): RedirectResponse
    {
        return $this->handleCompilation($request, $library, 'compile');
    }

    /**
     * @param GenericCompileRequest $request
     * @param Library               $library
     *
     * @return RedirectResponse
     */
    public function decompile(GenericCompileRequest $request, Library $library): RedirectResponse
    {
        return $this->handleCompilation($request, $library, 'decompile');
    }

    /**
     * Processes the compilation/decompilation and sets up the jobs.
     */
    private function handleCompilation(
        GenericCompileRequest $request,
        Library $library,
        string $action
    ): RedirectResponse {
        /** @var Location */
        $location = Location::findOrFail($request->input('location_id'));
        $domainIds = $request->input('legal_domains', []);
        $includeChildLocations = (bool) $request->input('include_children', false);

        GenericCompilation::dispatch($library->id, $location->id, $includeChildLocations, $domainIds, $action);

        return redirect()->route('my.settings.libraries.show', ['library' => $library->id]);
    }
}
