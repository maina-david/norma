<?php

namespace App\Http\Controllers\Corpus\My\Settings;

use App\Http\Controllers\Controller;
use App\Models\Compilation\Library;
use App\Models\Corpus\Work;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class ForLibraryWorkController extends Controller
{
    /**
     * @param Library $library
     *
     * @return Response
     */
    public function index(Library $library): Response
    {
        $inLibrary = function ($q) use ($library) {
            $q->whereRelation('libraries', 'id', $library->id);
        };
        $baseQuery = Work::whereHas('references', $inLibrary)
            ->with(['references' => $inLibrary, 'references.citation']);

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.compilation.my.library.works-for-library',
            'target' => 'settings-works-for-library-' . $library->id,
            'baseQuery' => $baseQuery,
            'library' => $library,
        ]);

        return turboStreamResponse($view);
    }
}
