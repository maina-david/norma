<?php

namespace App\Http\Controllers\Compilation\My;

use App\Actions\Compilation\Autocompilation\HandleAutoCompilationExcelReportExport;
use App\Enums\Auth\UserActivityType;
use App\Enums\Compilation\ContextQuestionAnswer;
use App\Events\Auth\UserActivity\FilteredResource;
use App\Events\Auth\UserActivity\GenericActivity;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DecodesHashids;
use App\Http\Controllers\Traits\PerformsActions;
use App\Http\Controllers\Traits\UsesLibryoWithContextQuestion;
use App\Http\ResourceActions\Compilation\ApplicabilityBulkAnswer;
use App\Jobs\Compilation\AutoCompilationExcelImport;
use App\Models\Actions\ActionArea;
use App\Models\Assess\AssessmentItem;
use App\Models\Auth\User;
use App\Models\Compilation\ContextQuestion;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Services\Customer\ActiveLibryosManager;
use App\Services\Storage\MimeTypeManager;
use App\Services\TempFileManager;
use App\Stores\Compilation\ContextQuestionLibryoStore;
use Exception;
use HotwiredLaravel\TurboLaravel\Http\MultiplePendingTurboStreamResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

class ContextQuestionController extends Controller
{
    use PerformsActions;
    use DecodesHashids;
    use UsesLibryoWithContextQuestion;

    public function __construct(protected ContextQuestionLibryoStore $contextQuestionLibryoStore)
    {
    }

    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless($user->canManageApplicability(), 404);

        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        $organisation = $manager->getActiveOrganisation();

        $answers = is_array($request->query('answer')) ? $request->query('answer') : [];

        $forLibryos = fn ($query) => $query->active()->where('organisation_id', $organisation->id);

        $locationQuery = Libryo::where('organisation_id', $organisation->id)->select('location_id');

        $descriptionQuery = function ($locationArray) {
            return function ($query) use ($locationArray) {
                $locations = DB::table('corpus_location_closure')
                    ->whereIn('descendant', $locationArray)
                    ->pluck('ancestor');

                return $query
                    ->where(fn ($builder) => $builder->whereNull('location_id')->orWhereIn('location_id', $locations));
            };
        };

        $query = ContextQuestion::forOrganisationWithAnswers($organisation, $answers)
            ->with([
                'libryos' => $forLibryos,
                'mainDescription' => $descriptionQuery($locationQuery),
                'categories',
            ])
            ->withCount(['libryos' => $forLibryos])
            ->orderBy('libryos_count', 'DESC')
            ->orderBy('category_id');

        $libryo = null;
        if ($manager->isSingleMode() && $libryo = $manager->getActive()) {
            $query = ContextQuestion::forLibryoWithAnswers($libryo, $answers)
                ->with([
                    'libryos' => fn ($builder) => $builder->active()->where('id', $libryo->id),
                    'mainDescription' => $descriptionQuery([$libryo->location_id]),
                    'categories',
                ])
                ->with(['categories'])
                ->orderBy('category_id');
        }

        /** @var User $user */
        $user = $request->user();
        $filtered = $request->only(['categories', 'answer']);

        if (!empty($filtered)) {
            event(new FilteredResource($user, UserActivityType::filteredApplicability(), $filtered, $libryo, $organisation));
        }

        /** @var View */
        return view('pages.compilation.my.context-question.settings.index', [
            'baseQuery' => $query,
            'libryo' => $libryo,
        ]);
    }

    /**
     * Show the details of the context question in the organisation.
     *
     * @param string $question
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(string $question): View|RedirectResponse
    {
        /** @var ContextQuestion $question */
        $question = $this->decodeHash($question, ContextQuestion::class);

        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        $organisation = $manager->getActiveOrganisation();

        /** @var User $user */
        $user = Auth::user();

        $libryo = $manager->getActive();

        if ($manager->isSingleMode() && $libryo) {
            return redirect()->route('my.context-questions.libryo.show', ['question' => $question->hash_id, 'libryo' => $libryo->hash_id]);
        }

        event(new GenericActivity($user, UserActivityType::viewedApplicabilityQuestion(), null, $libryo, $organisation));

        /** @var View */
        return view('pages.compilation.my.context-question.settings.show', [
            'baseQuery' => $question->libryos()->active()->where('organisation_id', $organisation->id),
            'question' => $question,
        ]);
    }

    /**
     * Get the context question details for the libryo.
     *
     * @param string $question
     * @param string $libryo
     *
     * @return View
     */
    public function showForLibryo(string $question, string $libryo): View
    {
        /** @var ContextQuestion $question */
        $question = $this->decodeHash($question, ContextQuestion::class);

        /** @var int $libryo */
        $libryo = $this->decodeHashId($libryo, Libryo::class);

        $libryo = $this->resolveLibryoFromContextQuestion($question, $libryo);

        $explanation = $question->explanationForLibryo($libryo);

        $question->load(['libryos' => fn ($query) => $query->whereKey($libryo->id)]);

        $libryoFilter = function ($builder) use ($libryo) {
            $builder->where('place_id', $libryo->id);
        };

        $question->loadCount([
            'references' => function ($builder) use ($libryo) {
                $builder->forLibryoAutocompiledBase($libryo);
            },
            'activities' => $libryoFilter,
            'tasks' => $libryoFilter,
            'comments' => $libryoFilter,
        ]);

        $assessmentItemsCount = $libryo->hasAssessModule() ? AssessmentItem::possibleForUncompiledLibryo($libryo, $question)->count() : 0;
        $actionsCount = $libryo->hasActionsModule() ? ActionArea::possibleForLibryoInApplicability($libryo, $question)->count() : 0;

        /** @var \App\Models\Customer\Pivots\ContextQuestionLibryo $pivot */
        $pivot = $question->libryos->first()?->pivot; // @phpstan-ignore-line
        $pivot->load(['lastAnsweredBy']);

        /** @var User $user */
        $user = Auth::user();

        event(new GenericActivity($user, UserActivityType::viewedApplicabilityQuestion(), null, $libryo));

        /** @var View */
        return view('pages.compilation.my.context-question.show-for-libryo', [
            'libryo' => $libryo,
            'question' => $question,
            'explanation' => $explanation,
            'answer' => $pivot,
            'assessmentItemsCount' => $assessmentItemsCount,
            'actionsCount' => $actionsCount,
        ]);
    }

    /**
     * Perform a given action on a given list of libryo streams.
     *
     * @param Request         $request
     * @param ContextQuestion $question
     *
     * @return RedirectResponse
     */
    public function actionsForQuestion(Request $request, ContextQuestion $question): RedirectResponse
    {
        $actionName = $this->validateActionName($request);
        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        /** @var Organisation */
        $organisation = $manager->getActiveOrganisation();

        /** @var \Illuminate\Database\Eloquent\Collection<Libryo> */
        $libryos = $this->filterActionInputForOrg($request, Libryo::class, $organisation);

        $this->performActionForQuestion($request, $actionName, $question, $libryos);

        return back();
    }

    /**
     * @param Request            $request
     * @param string             $action
     * @param ContextQuestion    $question
     * @param Collection<Libryo> $libryos
     *
     * @return void
     */
    private function performActionForQuestion(Request $request, string $action, ContextQuestion $question, Collection $libryos): void
    {
        /** @var User $user */
        $user = $request->user();
        switch ($action) {
            case 'applicability_answer_yes':
                $this->contextQuestionLibryoStore->answerQuestionForLibryos($question, $libryos, ContextQuestionAnswer::yes(), $user);
                break;
            case 'applicability_answer_no':
                $this->contextQuestionLibryoStore->answerQuestionForLibryos($question, $libryos, ContextQuestionAnswer::no(), $user);
                break;
                // @codeCoverageIgnoreStart
            default:
                abort(422);
                // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Perform a given action on a given list of libryo streams.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\HotwiredLaravel\TurboLaravel\Http\MultiplePendingTurboStreamResponse
     */
    public function indexActions(Request $request): RedirectResponse|MultiplePendingTurboStreamResponse
    {
        /** @var \Illuminate\Database\Eloquent\Collection<ContextQuestion> */
        $questions = $this->filterActionInput($request, ContextQuestion::class, fn ($query) => $query->select('id'));

        $confirm = (new ApplicabilityBulkAnswer(fn ($request) => $this->handleIndexActions($request)))
            ->trigger($request, $questions->pluck('id')->all());

        /** @var User $user */
        $user = Auth::user();

        return $user->getSetting('context_hide_duplicate_notice', false) ? $this->handleIndexActions($request) : $confirm;
    }

    protected function handleIndexActions(Request $request): RedirectResponse
    {
        $actionName = $this->validateActionName($request);
        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        /** @var Organisation */
        $organisation = $manager->getActiveOrganisation();
        $libryosQuery = fn ($query) => $query->where('organisation_id', $organisation->id);

        if ($manager->isSingleMode() && $libryo = $manager->getActive()) {
            // @codeCoverageIgnoreStart
            $libryosQuery = fn ($query) => $query->where('id', $libryo->id);
            // @codeCoverageIgnoreEnd
        }

        /** @var \Illuminate\Database\Eloquent\Collection<ContextQuestion> */
        $questions = $this->filterActionInput($request, ContextQuestion::class, fn ($query) => $query->whereHas('libryos', $libryosQuery));

        $questions->load(['libryos' => $libryosQuery]);

        $this->performIndexAction($request, $actionName, $questions);

        $this->notifySuccessfulUpdate();

        return back();
    }

    /**
     * @param Request                     $request
     * @param string                      $action
     * @param Collection<ContextQuestion> $questions
     *
     * @return void
     */
    private function performIndexAction(Request $request, string $action, Collection $questions): void
    {
        /** @var User $user */
        $user = $request->user();

        switch ($action) {
            case 'applicability_answer_yes':
                foreach ($questions as $question) {
                    $this->contextQuestionLibryoStore->answerQuestionForLibryos($question, $question->libryos, ContextQuestionAnswer::yes(), $user);
                }
                break;
            case 'applicability_answer_no':
                foreach ($questions as $question) {
                    $this->contextQuestionLibryoStore->answerQuestionForLibryos($question, $question->libryos, ContextQuestionAnswer::no(), $user);
                }
                break;
                // @codeCoverageIgnoreStart
            default:
                abort(422);
                // @codeCoverageIgnoreEnd
        }

        if (in_array($action, ['applicability_answer_yes', 'applicability_answer_no'])) {
            $manager = app(ActiveLibryosManager::class);

            event(new GenericActivity(
                $user,
                UserActivityType::bulkAnsweredApplicability(),
                null,
                $manager->getActive(),
                $manager->getActiveOrganisation()
            ));
        }
    }

    public function import(): View
    {
        /** @var View */
        return view('pages.compilation.my.context-question.settings.import', []);
    }

    /**
     * Bulk import for context questions.
     *
     * @param Request $request
     *
     * @throws Exception
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function uploadExcelImport(Request $request): Response|RedirectResponse
    {
        $manager = app(ActiveLibryosManager::class);
        /** @var Organisation|null */
        $organisation = $manager->getActiveOrganisation();
        if (!$organisation) {
            // @codeCoverageIgnoreStart
            return redirect()->route('my.settings.dashboard');
            // @codeCoverageIgnoreEnd
        }

        /** @var UploadedFile|null */
        $file = $request->file('file');
        if (is_null($file)) {
            Session::flash('flash.type', 'error');
            Session::flash('flash.message', __('compilation.context_brief.error_no_file'));

            return back();
        }

        $mimes = app(MimeTypeManager::class)->getAcceptedExcelMimes();

        if (!in_array($file->getClientMimeType(), $mimes)) {
            // @codeCoverageIgnoreStart
            /** @var string */
            $m = __('exceptions.unsupported_media_type');
            throw new UnsupportedMediaTypeHttpException($m);
            // @codeCoverageIgnoreEnd
        }

        /** @var string $filePath */
        $filePath = app(TempFileManager::class)->storeWithRandomName($file);
        /** @var User $user */
        $user = $request->user();

        $job = new AutoCompilationExcelImport($organisation, $filePath, $user);
        $this->dispatch($job);

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.system.file-download-progress',
            'target' => 'download-progress',
            'jobId' => $job->getJobStatusId(),
            'percentage' => 0,
            'redirect' => route('my.context-questions.index'),
            'upload' => true,
        ]);

        event(new GenericActivity(
            $user,
            UserActivityType::uploadedApplicabilityTemplate(),
            null,
            $manager->getActive(),
            $organisation
        ));

        return turboStreamResponse($view);
    }

    /**
     * Get the bulk export of all context questions for an organisation.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function export(Request $request): RedirectResponse
    {
        $manager = app(ActiveLibryosManager::class);
        /** @var Organisation|null */
        $organisation = $manager->getActiveOrganisation();
        if (!$organisation) {
            // @codeCoverageIgnoreStart
            return redirect()->route('my.settings.dashboard');
            // @codeCoverageIgnoreEnd
        }
        /** @var User $user */
        $user = $request->user();

        /** @var Libryo|null $libryo */
        $libryo = $manager->getActive();

        HandleAutoCompilationExcelReportExport::dispatch($organisation->id, $user->id, $libryo->id ?? null);
        Session::flash('flash.message', __('compilation.context_brief.email_will_be_sent'));

        event(new GenericActivity($user, UserActivityType::downloadedApplicabilityTemplate(), null, $libryo, $organisation));

        return redirect()->route('my.context-questions.index');
    }
}
