<?php

namespace App\Http\Controllers\Corpus\My\Settings;

use App\Http\Controllers\Controller;
use App\Models\Corpus\Work;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class ForManualCompilationWorkController extends Controller
{
    /**
     * @return Response
     */
    public function index(): Response
    {
        $baseQuery = Work::with([
            'references' => fn ($q) => $q->compilable()->typeCitation(),
            'references.locations' => fn ($q) => null,
        ])
            ->withCount(['children'])
            ->active();

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.corpus.work.my.for-manual-compilation',
            'target' => 'works-for-manual-compilation',
            'baseQuery' => $baseQuery,
        ]);

        return turboStreamResponse($view);
    }
}
