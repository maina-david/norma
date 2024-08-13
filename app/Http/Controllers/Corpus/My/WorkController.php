<?php

namespace App\Http\Controllers\Corpus\My;

use App\Enums\Auth\UserActivityType;
use App\Enums\Corpus\WorkStatus;
use App\Events\Auth\UserActivity\GenericActivity;
use App\Events\Auth\UserActivity\KnowYourLawActivity;
use App\Events\Auth\UserActivity\UserViewedTag;
use App\Exports\Requirements\LegalReportExcelExport;
use App\Http\Controllers\Corpus\ContentResourceController;
use App\Http\Controllers\Traits\GetsLibryoAndOrganisation;
use App\Jobs\Exports\GenerateLegalReportExcel;
use App\Jobs\Exports\GenerateLegalReportPDF;
use App\Models\Auth\User;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Ontology\Tag;
use App\Services\Customer\ActiveLibryosManager;
use App\Services\Search\Elastic\DocumentSearch;
use App\Services\Search\Meilisearch\Highlighter;
use App\Services\Storage\WorkStorageProcessor;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WorkController extends AbstractWorkController
{
    use GetsLibryoAndOrganisation;

    public function __construct(protected LegalReportExcelExport $excelExport, protected DocumentSearch $documentSearch)
    {
    }

    // /**
    //  * @param Request $request
    //  *
    //  * @return View
    //  */
    // public function requirements(Request $request): View
    // {
    //     $filters = $request->only(['tags', 'domains', 'jurisdictionTypes']);
    //     /** @var ActiveLibryosManager */
    //     $manager = app(ActiveLibryosManager::class);
    //     /** @var User */
    //     $user = Auth::user();
    //     $organisation = $libryo = null;
    //     if ($manager->isSingleMode()) {
    //         /** @var Libryo */
    //         $libryo = $manager->getActive();
    //         $libryo->load('location');
    //         $query = Work::forLibryo($libryo, $filters);
    //     } else {
    //         /** @var Organisation */
    //         $organisation = $manager->getActiveOrganisation();
    //         $query = Work::forOrganisationUserAccess($organisation, $user, $filters);
    //     }

    //     $query->filter($request->only('search'))
    //         ->with(['primaryLocation', 'parents']);

    //     /** @var array<int|string> */
    //     $filteredTagIds = $request->input('tags', []);
    //     // the routes to route to when closing tags
    //     $filteredTagRoutes = [];
    //     $currentFilters = $request->except(['page', 'perPage']);
    //     foreach ($filteredTagIds as $id) {
    //         $filters = array_diff_key($currentFilters, ['tags']);
    //         $filters['tags'] = array_filter($filteredTagIds, fn ($tId) => $tId !== $id);
    //         $filteredTagRoutes[$id] = route('my.corpus.requirements.index', $filters);
    //     }

    //     $filteredTags = !empty($filteredTagIds)
    //         ? Tag::whereKey($filteredTagIds)->get(['id', 'title'])
    //         : (new Tag())->newCollection();

    //     /** @var User $user */
    //     $user = Auth::user();
    //     foreach ($filteredTags as $tag) {
    //         UserViewedTag::dispatch($user, $tag, $libryo, $organisation);
    //     }

    //     KnowYourLawActivity::dispatch($user, UserActivityType::viewedRequirements(), null, $libryo, $organisation);

    //     /** @var View */
    //     return view('pages.corpus.work.my.requirements', [
    //         'baseQuery' => $query->orderBy('title'),
    //         'libryo' => $libryo ?? null,
    //         'organisation' => $organisation ?? null,
    //     ]);
    // }

    /**
     * NB: To replace requirements and legal register views.
     *
     * @param ActiveLibryosManager $manager
     * @param Request              $request
     *
     * @return View
     */
    public function index(ActiveLibryosManager $manager, Request $request): View
    {
        /** @var User */
        $user = $request->user();
        $filters = $request->only(['tags', 'topics', 'controls', 'domains', 'jurisdictionTypes', 'works', 'bookmarked']);

        if (isset($filters['topics'])) {
            // @codeCoverageIgnoreStart
            $filters['descendantTopics'] = $filters['topics'];
            // @codeCoverageIgnoreEnd
        }

        $organisation = $libryo = null;
        if ($manager->isSingleMode()) {
            /** @var Libryo */
            $libryo = $manager->getActive();
        } else {
            /** @var Organisation */
            $organisation = $manager->getActiveOrganisation();

            /** @var View */
            return view('pages.corpus.work.my.index', [
                'libryo' => null,
                'organisation' => $organisation,
            ]);
        }

        /** @var string */
        $search = $request->input('search', '');
        $query = Work::with([
            'references.bookmarks' => fn ($query) => $query->forUser($user)->select(['reference_id']),
            'children.references.bookmarks' => fn ($query) => $query->forUser($user)->select(['reference_id']),
        ])
            ->forRequirements($filters, $search, $user, $libryo, $organisation, false);

        /** @var array<int|string> */
        $filteredTagIds = $request->input('tags', []);
        /** @var \Illuminate\Database\Eloquent\Collection<int, Tag> $filteredTags */
        $filteredTags = !empty($filteredTagIds)
            ? Tag::whereKey($filteredTagIds)->get(['id', 'title'])
            : (new Tag())->newCollection();

        foreach ($filteredTags as $tag) {
            UserViewedTag::dispatch($user, $tag, $libryo, $organisation);
        }

        KnowYourLawActivity::dispatch($user, UserActivityType::viewedRequirements(), null, $libryo, $organisation);

        $filtersApplied = !empty($filters) || !empty($search);

        /** @var LengthAwarePaginator<Work> */
        $works = $query->paginate(10)->withQueryString();
        if (!empty($search)) {
            $works = app(Highlighter::class)->highlightForWorks($works, $search);
            KnowYourLawActivity::dispatch($user, UserActivityType::fulltextSearchTerm(), ['term' => $search], $libryo, $organisation);
        }

        /** @var View */
        return view('pages.corpus.work.my.index', [
            'works' => $works,
            'libryo' => $libryo,
            'filtersApplied' => $filtersApplied,
            'organisation' => $organisation,
            'filters' => !empty($search) ? ['search' => $search, ...$filters] : $filters,
        ]);
    }

    public function legalRegister(Request $request): View
    {
        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        $organisation = $libryo = $query = null;
        $filters = $request->only(['domains', 'jurisdictionTypes']);
        if ($manager->isSingleMode()) {
            /** @var Libryo */
            $libryo = $manager->getActive();
            $libryo->load('location');
            $query = Work::primaryForLibryo($libryo, $filters)
                ->withRelationsForLibryo($libryo, false, $filters);
            $organisation = $manager->getActiveOrganisation();
        } else {
            /** @var Organisation */
            $organisation = $manager->getActiveOrganisation();
            // $user = $request->user();
            // $query = Work::primaryForOrganisation($organisation, $user, $filters)
            //     ->withRelationsForOrganisation($organisation, $user, $filters);
        }

        // $query->with([
        //     'references' => function ($q) use ($libryo) {
        //         $q->forLibryo($libryo);
        //     },
        //     'references.locations' => fn ($q) => $q->select(['id', 'flag', 'title']),
        //     'references.citation' => fn () => null,
        //     'children' => function ($q) use ($libryo) {
        //         $q->forLibryo($libryo);
        //     },
        //     'children.references' => function ($q) use ($libryo) {
        //         $q->forLibryo($libryo);
        //     },
        //     'children.references.locations' => fn ($q) => $q->select(['id', 'flag', 'title']),
        //     'children.references.citation' => fn () => null,
        // ]);

        /** @var User $user */
        $user = Auth::user();
        KnowYourLawActivity::dispatch($user, UserActivityType::viewedRequirements(), null, $libryo, $organisation);

        /** @var View */
        return view('pages.corpus.work.my.legal-register', [
            'baseQuery' => $query ? $query->orderBy('title') : null,
            'libryo' => $libryo ?? null,
            'organisation' => $organisation,
            'filters' => $filters,
        ]);
    }

    /**
     * @param Request $request
     * @param Work    $work
     *
     * @return View
     */
    public function show(Request $request, Work $work): View
    {
        abort_if(WorkStatus::pending()->is($work->status) || WorkStatus::delete()->is($work->status), 404);

        $callback = $this->getReferenceSubQuery($request);

        $work->load([
            'children' => $callback,
            'parents' => fn () => null,
            'references' => $callback,
            'references.locations',
            'source',
        ]);

        /** @var User $user */
        $user = Auth::user();
        [$libryo, $organisation] = $this->getActiveLibryoAndOrganisation();
        KnowYourLawActivity::dispatch($user, UserActivityType::viewedFullText(), ['register_id' => $work->id], $libryo, $organisation);

        /** @var View */
        return view('pages.corpus.work.my.show', [
            'work' => $work,
            'view' => $request->input('view', 'requirements'),
            'preview' => $work->references()->active()->count() === 0,
            'showSource' => (bool) $work->getCurrentExpression()?->show_source_document,
        ]);
    }

    /**
     * @param Request                      $request
     * @param \App\Models\Corpus\Reference $reference
     *
     * @return Response
     */
    public function showForReference(Request $request, Reference $reference): Response
    {
        $reference->load(['work.source']);

        abort_if(WorkStatus::pending()->is($reference->work->status) || WorkStatus::delete()->is($reference->work->status), 404);

        // @codeCoverageIgnoreStart
        if ($reference->work->source->hide_content ?? false) {
            $reference->load(['htmlContent', 'childReferences.htmlContent']);

            /** @var View $view */
            $view = view('streams.single-partial', [
                'partialView' => 'partials.corpus.reference.my.reference-html-content',
                'target' => "corpus-work-for-reference-{$reference->id}",
                'reference' => $reference,
                'work' => $reference->work,
                'preview' => $reference->work->references()->active()->count() === 0,
                'showSource' => (bool) $reference->work->getCurrentExpression()?->show_source_document,
                'fluid' => true,
                'scrollTo' => $reference->id,
            ]);

            return turboStreamResponse($view);
        }
        // @codeCoverageIgnoreEnd

        /** @var ActiveLibryosManager */
        $callback = $this->getReferenceSubQuery($request);

        $reference->work->load([
            'children' => $callback,
            'parents' => fn () => null,
            'references' => $callback,
            'references.locations',
            'source',
        ]);

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'pages.corpus.work.my.work-content',
            'target' => "corpus-work-for-reference-{$reference->id}",
            'work' => $reference->work,
            'preview' => $reference->work->references()->active()->count() === 0,
            'showSource' => (bool) $reference->work->getCurrentExpression()?->show_source_document,
            'fluid' => true,
            'scrollTo' => $reference->id,
            'maxHeight' => 'h-[70vh] max-h-[70vh]',
        ]);

        return turboStreamResponse($view);
    }

    /**
     * @param Work        $work
     * @param string|null $file
     *
     * @return BinaryFileResponse|Response
     */
    public function previewSource(Work $work, ?string $file = null): BinaryFileResponse|Response
    {
        if ($work->activeDoc) {
            $path = $work->activeDoc->firstContentResource?->path;
            if (!$path) {
                // @codeCoverageIgnoreStart
                return response(__('corpus.work.no_preview_available'), 200)
                    ->header('Content-Type', 'text/plain');
                // @codeCoverageIgnoreEnd
            }

            return app(ContentResourceController::class)->streamContent($path);
        }
        $currentExpression = $work->getCurrentExpression();

        if (!$currentExpression || !$currentExpression->sourceDocument) {
            return response(__('corpus.work.no_preview_available'), 200)
                ->header('Content-Type', 'text/plain');
        }

        $storagePath = app(WorkStorageProcessor::class)->expressionPathSource($currentExpression);

        $path = $file ? $storagePath . DIRECTORY_SEPARATOR . $file : $currentExpression->sourceDocument->path;
        $disk = WorkStorageProcessor::disk();
        if (Storage::disk($disk)->missing($path)) {
            throw new NotFoundHttpException();
        }

        $returnFile = Storage::disk($disk)->get($path);
        $tmpFile = storage_path('app') . DIRECTORY_SEPARATOR . Str::random(80);
        file_put_contents($tmpFile, $returnFile);
        $headers = [];

        if (!$file) {
            $headers['Content-Type'] = $currentExpression->sourceDocument->mime_type;
        }
        if ($file && Str::endsWith($file, '.css')) {
            $headers['Content-Type'] = 'text/css';
        }

        return response()->file($tmpFile, $headers)->deleteFileAfterSend();
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function exportAsExcel(Request $request): Response
    {
        $filename = Str::random(15) . '.xlsx';
        $filters = $request->only(['domains', 'jurisdictionTypes', 'tags', 'works', 'search', 'bookmarked', 'descendantTopics', 'controls']);

        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        /** @var \App\Models\Auth\User $user */
        $user = $request->user();
        $libryo = $organisation = null;
        $organisation = $manager->getActiveOrganisation();

        if ($manager->isSingleMode()) {
            /** @var Libryo $libryo */
            $libryo = $manager->getActive();
            $filename = "l---{$libryo->id}---{$filename}";
            $job = new GenerateLegalReportExcel($filename, $user, $libryo, $organisation, $filters);
        } else {
            $filename = "o---{$organisation->id}---{$filename}";
            $job = new GenerateLegalReportExcel($filename, $user, null, $organisation, $filters);
        }

        $this->dispatch($job);

        event(new GenericActivity(
            $user,
            UserActivityType::exportedLegalRegister(),
            $filters,
            $manager->getActive(),
            $manager->getActiveOrganisation()
        ));

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.system.file-download-progress',
            'target' => 'download-progress',
            'jobId' => $job->getJobStatusId(),
            'percentage' => 0,
            'redirect' => route('my.downloads.download.requirements.excel', ['filename' => $filename], false),
        ]);

        return turboStreamResponse($view);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function exportAsPDF(Request $request): Response
    {
        $filename = Str::random(15) . '.pdf';
        $filters = $request->only(['domains', 'jurisdictionTypes', 'tags', 'works', 'search', 'bookmarked', 'descendantTopics', 'controls']);

        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        /** @var \App\Models\Auth\User $user */
        $user = $request->user();
        if ($manager->isSingleMode()) {
            /** @var Libryo $libryo */
            $libryo = $manager->getActive();
            $filename = "l---{$libryo->id}---{$filename}";
            $job = new GenerateLegalReportPDF($filename, $user, $libryo, null, $filters);
            $this->dispatch($job);
        } else {
            /** @var Organisation $organisation */
            $organisation = $manager->getActiveOrganisation();
            $filename = "o---{$organisation->id}---{$filename}";
            $job = new GenerateLegalReportPDF($filename, $user, null, $organisation, $filters);
            $this->dispatch($job);
        }

        event(new GenericActivity(
            $user,
            UserActivityType::exportedLegalRegister(),
            $filters,
            $manager->getActive(),
            $manager->getActiveOrganisation()
        ));

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.system.file-download-progress',
            'target' => 'download-progress',
            'jobId' => $job->getJobStatusId(),
            'percentage' => 0,
            'redirect' => route('my.downloads.download.requirements.pdf', ['filename' => $filename], false),
        ]);

        return turboStreamResponse($view);
    }

    /**
     * @param Request $request
     * @param Work    $work
     *
     * @return View
     */
    public function printPreview(Request $request, Work $work): View
    {
        if (!$expression = $work->getCurrentExpression()) {
            // @codeCoverageIgnoreStart
            abort(404);
            // @codeCoverageIgnoreEnd
        }

        $references = $work->references()
            ->active()
            ->with(['htmlContent', 'refPlainText'])
            ->withCount('children')
            ->paginate(1000);

        $html = view('partials.corpus.reference.my.content-group', ['work' => $work, 'references' => $references])
            ->render();

        /** @var User $user */
        $user = $request->user();
        $manager = app(ActiveLibryosManager::class);

        event(new GenericActivity(
            $user,
            UserActivityType::printedRequirementDocument(),
            ['id' => $work->id],
            $manager->getActive(),
            $manager->getActiveOrganisation()
        ));

        /** @var View */
        return view('pages.corpus.work.my.print-preview', ['html' => $html]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return Closure
     */
    protected function getReferenceSubQuery(Request $request): Closure
    {
        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        $organisation = $libryo = null;
        if ($manager->isSingleMode()) {
            /** @var Libryo */
            $libryo = $manager->getActive();
            $callback = function ($q) use ($libryo) {
                $q->forLibryo($libryo);
            };
        } else {
            /** @var Organisation */
            $organisation = $manager->getActiveOrganisation();
            $user = $request->user();
            $callback = function ($q) use ($organisation, $user) {
                $q->forOrganisationUserAccess($organisation, $user);
            };
        }

        return $callback;
    }
}
