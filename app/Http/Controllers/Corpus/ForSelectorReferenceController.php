<?php

namespace App\Http\Controllers\Corpus;

use App\Enums\Application\ApplicationType;
use App\Http\Controllers\Controller;
use App\Managers\AppManager;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

class ForSelectorReferenceController extends Controller
{
    /**
     * @param Request $request
     * @param Work    $work
     *
     * @return Response
     */
    public function index(Request $request, Work $work): Response
    {
        $baseQuery = Reference::typeCitation()
            ->where('work_id', $work->id)
            ->active()
            ->with(['citation:id,number,heading', 'refSelector:reference_id,selectors', 'refPlainText'])
            ->filter($request->only(['search']))
            ->when(ApplicationType::my()->is(AppManager::getApp()), function ($builder) {
                $builder->compilable();
            })
            ->when(ApplicationType::collaborate()->is(AppManager::getApp()), function ($builder) use ($request) {
                $builder->filter($request->only(['has-consequence', 'has-requirement']))
                    ->orderBy('volume')
                    ->orderBy('start')
                    ->orderBy('type', 'desc');
            });

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.corpus.reference.for-selector',
            'target' => 'multi-reference-selector-references-for-work',
            'baseQuery' => $baseQuery,
            'route' => route(Route::current()?->getName() ?? '', ['work' => $work->id]),
            'withSelectAll' => $request->has('select-all'),
        ]);

        return turboStreamResponse($view);
    }

    /**
     * Get the cached content for the given reference.
     *
     * @param Reference $reference
     *
     * @return PendingTurboStreamResponse
     */
    public function cachedContent(Reference $reference): PendingTurboStreamResponse
    {
        return singleTurboStreamResponse("selector_reference_{$reference->id}")
            ->view('partials.corpus.reference.cached-content', ['reference' => $reference->load(['htmlContent'])]);
    }
}
