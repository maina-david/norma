<?php

namespace App\Http\Controllers\Corpus;

use App\Enums\Application\ApplicationType;
use App\Http\Controllers\Controller;
use App\Managers\AppManager;
use App\Models\Corpus\Work;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

class ForSelectorWorkController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request): Response
    {
        $baseQuery = Work::with([
            'references' => fn ($q) => $q->compilable()->typeCitation(),
            'references.locations' => fn ($q) => null,
        ])->when(ApplicationType::my()->is(AppManager::getApp()), function ($builder) {
            $builder->active();
        });

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.corpus.work.for-selector',
            'target' => $request->header('turbo-frame'),
            'baseQuery' => $baseQuery,
            'route' => route(Route::current()?->getName() ?? ''),
        ]);

        return turboStreamResponse($view);
    }
}
