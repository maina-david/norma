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
use App\Http\Controllers\Traits\UsesNormaWithContextQuestion;
use App\Http\ResourceActions\Compilation\ApplicabilityBulkAnswer;
use App\Jobs\Compilation\AutoCompilationExcelImport;
use App\Models\Actions\ActionArea;
use App\Models\Assess\AssessmentItem;
use App\Models\Auth\User;
use App\Models\Compilation\ContextQuestion;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Services\Customer\ActiveNormasManager;
use App\Services\Storage\MimeTypeManager;
use App\Services\TempFileManager;
use App\Stores\Compilation\ContextQuestionNormaStore;
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
    use UsesNormaWithContextQuestion;

    public function __construct(protected ContextQuestionNormaStore $contextQuestionNormaStore)
    {
    }

    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless($user->canManageApplicability(), 404);

        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        $organisation = $manager->getActiveOrganisation();

        $answers = is_array($request->query('answer')) ? $request->query('answer') : [];

        $forNormas = fn ($query) => $query->active()->where('organisation_id', $organisation->id);

        $locationQuery = Norma::where('organisation_id', $organisation->id)->select('location_id');

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
                'normas' => $forNormas,
                'mainDescription' => $descriptionQuery($locationQuery),
                'categories',
            ])
            ->withCount(['normas' => $forNormas])
            ->orderBy('normas_count', 'DESC')
            ->orderBy('category_id');

        $norma = null;
        if ($manager->isSingleMode() && $norma = $manager->getActive()) {
            $query = ContextQuestion::forNormaWithAnswers($norma, $answers)
                ->with([
                    'normas' => fn ($builder) => $builder->active()->where('id', $norma->id),
                    'mainDescription' => $descriptionQuery([$norma->location_id]),
                    'categories',
                ])
                ->with(['categories'])
                ->orderBy('category_id');
        }

        /** @var User $user */
        $user = $request->user();
        $filtered = $request->only(['categories', 'answer']);

        if (!empty($filtered)) {
            event(new FilteredResource($user, UserActivityType::filteredApplicability(), $filtered, $norma, $organisation));
        }

        /** @var View */
        return view('pages.compilation.my.context-question.settings.index', [
            'baseQuery' => $query,
            'norma' => $norma,
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

        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        $organisation = $manager->getActiveOrganisation();

        /** @var User $user */
        $user = Auth::user();

        $norma = $manager->getActive();

        if ($manager->isSingleMode() && $norma) {
            return redirect()->route('my.context-questions.norma.show', ['question' => $question->hash_id, 'norma' => $norma->hash_id]);
        }

        event(new GenericActivity($user, UserActivityType::viewedApplicabilityQuestion(), null, $norma, $organisation));

        /** @var View */
        return view('pages.compilation.my.context-question.settings.show', [
            'baseQuery' => $question->normas()->active()->where('organisation_id', $organisation->id),
            'question' => $question,
        ]);
    }

    /**
     * Get the context question details for the norma.
     *
     * @param string $question
     * @param string $norma
     *
     * @return View
     */
    public function showForNorma(string $question, string $norma): View
    {
        /** @var ContextQuestion $question */
        $question = $this->decodeHash($question, ContextQuestion::class);

        /** @var int $norma */
        $norma = $this->decodeHashId($norma, Norma::class);

        $norma = $this->resolveNormaFromContextQuestion($question, $norma);

        $explanation = $question->explanationForNorma($norma);

        $question->load(['normas' => fn ($query) => $query->whereKey($norma->id)]);

        $normaFilter = function ($builder) use ($norma) {
            $builder->where('place_id', $norma->id);
        };

        $question->loadCount([
            'references' => function ($builder) use ($norma) {
                $builder->forNormaAutocompiledBase($norma);
            },
            'activities' => $normaFilter,
            'tasks' => $normaFilter,
            'comments' => $normaFilter,
        ]);

        $assessmentItemsCount = $norma->hasAssessModule() ? AssessmentItem::possibleForUncompiledNorma($norma, $question)->count() : 0;
        $actionsCount = $norma->hasActionsModule() ? ActionArea::possibleForNormaInApplicability($norma, $question)->count() : 0;

        /** @var \App\Models\Customer\Pivots\ContextQuestionNorma $pivot */
        $pivot = $question->normas->first()?->pivot; // @phpstan-ignore-line
        $pivot->load(['lastAnsweredBy']);

        /** @var User $user */
        $user = Auth::user();

        event(new GenericActivity($user, UserActivityType::viewedApplicabilityQuestion(), null, $norma));

        /** @var View */
        return view('pages.compilation.my.context-question.show-for-norma', [
            'norma' => $norma,
            'question' => $question,
            'explanation' => $explanation,
            'answer' => $pivot,
            'assessmentItemsCount' => $assessmentItemsCount,
            'actionsCount' => $actionsCount,
        ]);
    }

    /**
     * Perform a given action on a given list of norma streams.
     *
     * @param Request         $request
     * @param ContextQuestion $question
     *
     * @return RedirectResponse
     */
    public function actionsForQuestion(Request $request, ContextQuestion $question): RedirectResponse
    {
        $actionName = $this->validateActionName($request);
        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        /** @var Organisation */
        $organisation = $manager->getActiveOrganisation();

        /** @var \Illuminate\Database\Eloquent\Collection<Norma> */
        $normas = $this->filterActionInputForOrg($request, Norma::class, $organisation);

        $this->performActionForQuestion($request, $actionName, $question, $normas);

        return back();
    }

    /**
     * @param Request            $request
     * @param string             $action
     * @param ContextQuestion    $question
     * @param Collection<Norma> $normas
     *
     * @return void
     */
    private function performActionForQuestion(Request $request, string $action, ContextQuestion $question, Collection $normas): void
    {
        /** @var User $user */
        $user = $request->user();
        switch ($action) {
            case 'applicability_answer_yes':
                $this->contextQuestionNormaStore->answerQuestionForNormas($question, $normas, ContextQuestionAnswer::yes(), $user);
                break;
            case 'applicability_answer_no':
                $this->contextQuestionNormaStore->answerQuestionForNormas($question, $normas, ContextQuestionAnswer::no(), $user);
                break;
                // @codeCoverageIgnoreStart
            default:
                abort(422);
                // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Perform a given action on a given list of norma streams.
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
        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        /** @var Organisation */
        $organisation = $manager->getActiveOrganisation();
        $normasQuery = fn ($query) => $query->where('organisation_id', $organisation->id);

        if ($manager->isSingleMode() && $norma = $manager->getActive()) {
            // @codeCoverageIgnoreStart
            $normasQuery = fn ($query) => $query->where('id', $norma->id);
            // @codeCoverageIgnoreEnd
        }

        /** @var \Illuminate\Database\Eloquent\Collection<ContextQuestion> */
        $questions = $this->filterActionInput($request, ContextQuestion::class, fn ($query) => $query->whereHas('normas', $normasQuery));

        $questions->load(['normas' => $normasQuery]);

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
                    $this->contextQuestionNormaStore->answerQuestionForNormas($question, $question->normas, ContextQuestionAnswer::yes(), $user);
                }
                break;
            case 'applicability_answer_no':
                foreach ($questions as $question) {
                    $this->contextQuestionNormaStore->answerQuestionForNormas($question, $question->normas, ContextQuestionAnswer::no(), $user);
                }
                break;
                // @codeCoverageIgnoreStart
            default:
                abort(422);
                // @codeCoverageIgnoreEnd
        }

        if (in_array($action, ['applicability_answer_yes', 'applicability_answer_no'])) {
            $manager = app(ActiveNormasManager::class);

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
        $manager = app(ActiveNormasManager::class);
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
        $manager = app(ActiveNormasManager::class);
        /** @var Organisation|null */
        $organisation = $manager->getActiveOrganisation();
        if (!$organisation) {
            // @codeCoverageIgnoreStart
            return redirect()->route('my.settings.dashboard');
            // @codeCoverageIgnoreEnd
        }
        /** @var User $user */
        $user = $request->user();

        /** @var Norma|null $norma */
        $norma = $manager->getActive();

        HandleAutoCompilationExcelReportExport::dispatch($organisation->id, $user->id, $norma->id ?? null);
        Session::flash('flash.message', __('compilation.context_brief.email_will_be_sent'));

        event(new GenericActivity($user, UserActivityType::downloadedApplicabilityTemplate(), null, $norma, $organisation));

        return redirect()->route('my.context-questions.index');
    }
}
