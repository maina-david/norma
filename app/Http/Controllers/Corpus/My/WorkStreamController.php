<?php

namespace App\Http\Controllers\Corpus\My;

use App\Http\Controllers\Traits\UsesContentResource;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Ontology\Tag;
use App\Services\Corpus\VolumeHighlighter;
use App\Services\Customer\ActiveNormasManager;
use HotwiredLaravel\TurboLaravel\Http\MultiplePendingTurboStreamResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WorkStreamController extends AbstractWorkController
{
    use UsesContentResource;

    /**
     * @param Work $work
     *
     * @return Response
     */
    public function showWithRelations(Request $request, Work $work): Response
    {
        [$query, $locationTypeQuery, $norma, $organisation] = $this->getNormaOrganisationQuery($request);

        $user = $request->user();
        $applyCompilationQuery = function ($q) use ($organisation, $norma, $user) {
            if (is_null($norma)) {
                return $q->forOrganisationUserAccess($organisation, $user);
            }

            return $q->forNorma($norma);
        };

        $query = (new Work())->newQuery();
        $applyCompilationQuery($query);

        $work->load(['children' => function ($q) use ($applyCompilationQuery) {
            $applyCompilationQuery($q);
        }]);

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.corpus.work.with-relations',
            'target' => 'work-with-relations-' . $work->id,
            'work' => $work,
            'norma' => $norma,
            'showSelf' => $query->whereKey($work->id)->exists(),
        ]);

        return turboStreamResponse($view);
    }

    /**
     * @param Request $request
     * @param Work    $work
     *
     * @return Response|\HotwiredLaravel\TurboLaravel\Http\MultiplePendingTurboStreamResponse
     */
    public function fullText(Request $request, Work $work): Response|MultiplePendingTurboStreamResponse
    {
        if (!$expression = $work->getCurrentExpression()) {
            // @codeCoverageIgnoreStart
            abort(404);
            // @codeCoverageIgnoreEnd
        }

        if ($expression->doc) {
            $expression->doc->load(['docMeta', 'firstContentResource']);
            $scrollTo = $request->get('scroll_to');
            /** @var Reference $reference */
            $reference = Reference::with(['latestTocItem.contentResource'])
                ->where('work_id', $expression->work_id)
                ->find($scrollTo);

            $responses = [];

            // @codeCoverageIgnoreStart
            if ($reference->latestTocItem->contentResource ?? false) {
                /** @var \App\Models\Corpus\ContentResource $resource */
                $resource = $reference->latestTocItem?->contentResource;
                $responses[] = $this->getContentResource(
                    $resource,
                    $reference->latestTocItem?->doc_id ?? 0
                );
            }
            // @codeCoverageIgnoreEnd

            $responses[] = singleTurboStreamResponse("corpus-work-full-text-{$work->id}")
                ->view('partials.corpus.doc.render-doc-preview', [
                    'work' => $work,
                    'doc' => $expression->doc,
                    'scrollTo' => $reference->latestTocItem->source_unique_id ?? null,
                ]);

            return multipleTurboStreamResponse(array_reverse($responses));
        }

        $volume = (int) $request->input('volume', 1);

        $html = app(VolumeHighlighter::class)->highlight($expression, $volume);

        $countVolumes = $expression->volume;

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.corpus.work.full-text-container',
            'target' => 'corpus-work-full-text-' . $work->id,
            'fluid' => $request->get('fluid', false),
            'scrollTo' => $request->get('scroll_to', false),
            'work' => $work,
            'references' => $work->references()->active()->with(['citation', 'refPlainText'])->paginate(1000),
            'html' => preg_replace('/data-highlight-id="([0-9]+)"/', 'id="$1"', $html),
            'volume' => $volume,
            'page' => $request->input('page', 1),
            'previousVolumePage' => $countVolumes > 1 && $volume !== 1 ? route('my.works.full-text.show', ['work' => $work, 'volume' => $volume - 1]) : '',
            'nextVolumePage' => $countVolumes > 1 && $volume !== $countVolumes ? route('my.works.full-text.show', ['work' => $work, 'volume' => $volume + 1]) : '',
        ]);

        return turboStreamResponse($view);
    }

    /**
     * @param ActiveNormasManager $manager
     * @param Request              $request
     * @param string               $key     To distinguish between different frames if there are multiple frames on the same page
     *
     * @return Response
     */
    public function searchSuggestAll(ActiveNormasManager $manager, Request $request, string $key): Response
    {
        /** @var string */
        $search = $request->input('search', '');

        if ($manager->isSingleMode()) {
            /** @var Norma */
            $norma = $manager->getActive();
            $tag = Tag::forNorma($norma)->inRandomOrder()->first();
        } else {
            /** @var Organisation */
            $organisation = $manager->getActiveOrganisation();
            /** @var \App\Models\Auth\User $user */
            $user = $request->user();
            $tag = Tag::forOrganisationUserAccess($organisation, $user)->inRandomOrder()->first();
        }

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.corpus.work.my.search-suggest-all',
            'target' => 'corpus-requirements-search-suggest-' . $key,
            'search' => $search,
            'linkTarget' => $request->input('target'),
            'tag' => $tag,
        ]);

        return turboStreamResponse($view);
    }

    /**
     * Used for search suggest to search for works.
     *
     * @param Request $request
     * @param string  $key     To distinguish between different frames if there are multiple frames on the same page
     *
     * @return Response
     */
    public function searchSuggest(Request $request, string $key): Response
    {
        /** @var string */
        $search = $request->input('search', '');

        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        if ($manager->isSingleMode()) {
            /** @var Norma */
            $norma = $manager->getActive();
            /** @var Builder */
            $worksQuery = Work::cachedForNorma($norma);
        } else {
            /** @var Organisation */
            $organisation = $manager->getActiveOrganisation();
            /** @var \App\Models\Auth\User $user */
            $user = $request->user();
            /** @var Builder */
            $worksQuery = Work::cachedForOrganisationUserAccess($organisation, $user);
        }

        if ($search) {
            $worksQuery->where(fn ($q) => $q->titleLike($search)->orWhere('title_translation', 'like', "%{$search}%"));
        }

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.corpus.work.my.search-suggest',
            'target' => 'search-suggest-works-' . $key,
            'works' => $search ? $worksQuery->active()->with('parents')->limit(50)->get() : (new Work())->newCollection(),
            'search' => $search,
            'linkToDetailed' => $key === 'reference-search' ? true : false,
        ]);

        return turboStreamResponse($view);
    }

    /**
     * @param Work $work
     *
     * @return Response
     */
    public function fullTextFromReferences(Work $work): Response
    {
        if (!$work->getCurrentExpression()) {
            // @codeCoverageIgnoreStart
            abort(404);
            // @codeCoverageIgnoreEnd
        }

        $work->load(['source']);

        $references = collect();

        if (!($work->source->hide_content ?? false)) {
            $references = $work->references()
                ->active()
                ->with(['htmlContent', 'refPlainText'])
                ->withCount('children')
                ->paginate(1000);
        }

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.corpus.reference.my.content-group',
            'target' => 'full-text-content',
            'work' => $work,
            'references' => $references,
        ]);

        return turboStreamResponse($view);
    }
}
