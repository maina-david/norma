<?php

namespace App\Http\Controllers\Compilation\My\Settings;

use App\Http\Controllers\Controller;
use App\Models\Compilation\Library;
use App\Models\Corpus\Reference;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class ForReferenceLibraryController extends Controller
{
    public function index(Reference $reference): Response
    {
        $baseQuery = Library::whereRelation('references', 'id', $reference->id);

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.compilation.my.library.for-reference',
            'target' => 'libraries-for-reference-' . $reference->id,
            'baseQuery' => $baseQuery,
            'reference' => $reference,
        ]);

        return turboStreamResponse($view);
    }
}
