<?php

namespace App\Http\Controllers\Assess\My;

use App\Actions\Assess\AssessmentItemResponse\AnswerAssessmentItemResponse;
use App\Enums\Assess\ResponseStatus;
use App\Enums\Auth\UserActivityType;
use App\Events\Auth\UserActivity\AssessmentItems\BulkAnsweredAssessmentItems;
use App\Events\Auth\UserActivity\AssessmentItems\ExportedAssessmentItems;
use App\Events\Auth\UserActivity\GenericActivity;
use App\Http\Controllers\Assess\My\Traits\UsesAssessmentResponseViews;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PerformsActions;
use App\Jobs\Exports\GenerateAssessMetricsExportExcel;
use App\Jobs\Exports\GenerateAssessResponsesExportExcel;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Services\Assess\AssessStatsService;
use App\Services\Customer\ActiveNormasManager;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class AssessmentItemResponseController extends Controller
{
    use PerformsActions;
    use UsesAssessmentResponseViews;

    /**
     * @param AssessStatsService $assessStatsService
     */
    public function __construct(protected AssessStatsService $assessStatsService)
    {
    }

    // can be removed if not reverted before monolith launch
    // public function showRelations(AssessmentItemResponse $aiResponse): Response
    // {
    //     $assessmentItem = $aiResponse->assessmentItem;
    //     $norma = $aiResponse->norma;
    //     $assessmentItem->loadCount([
    //         'references' => function ($q) use ($norma) {
    //             $q->forNorma($norma);
    //         },
    //     ]);

    //     /** @var View $view */
    //     $view = view('streams.single-partial', [
    //         'partialView' => 'partials.assess.my.assessment-item-response.relations',
    //         'target' => 'assessment-item-response-relations-' . $aiResponse->id,
    //         'assessmentItem' => $aiResponse->assessmentItem,
    //         'assessmentItemResponse' => $aiResponse->loadCount(['files', 'comments', 'tasks']),
    //     ]);

    //     return turboStreamResponse($view);
    // }

    /**
     * @param string $aiResponse
     *
     * @return View|RedirectResponse
     */
    public function show(string $aiResponse): View|RedirectResponse
    {
        /** @var AssessmentItemResponse */
        $aiResponse = $this->decodeHash($aiResponse, AssessmentItemResponse::class);
        $this->authorize('view', $aiResponse);
        $aiResponse->load(['assessmentItem.author', 'lastAnsweredBy']);
        /** @var \App\Models\Assess\AssessmentItem $assessmentItem */
        $assessmentItem = $aiResponse->assessmentItem;

        /** @var Norma $norma */
        $norma = $aiResponse->norma;

        if ($response = $this->redirectIfNoAssess($norma)) {
            return $response;
        }

        $norma->load(['location']);

        $assessmentItem->loadCount([
            'references' => function ($q) use ($norma) {
                $q->forNorma($norma);
            },
        ]);

        $fields = [
            'response_' . ResponseStatus::yes()->value,
            'response_' . ResponseStatus::no()->value,
            'response_' . ResponseStatus::notApplicable()->value,
            'response_' . ResponseStatus::notAssessed()->value,
            'mark_unchanged',
            'last_answered',
            'answered_by',
        ];

        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        $organisation = $manager->getActiveOrganisation();

        $answered = $assessmentItem->organisation_id === $organisation->id
            && $assessmentItem->assessmentResponses()->where('answer', '!=', ResponseStatus::draft()->value)->exists();

        /** @var View */
        return view('pages.assess.my.assessment-item-response.detail', [
            'response' => $aiResponse,
            'assessmentItem' => $assessmentItem,
            'organisation' => $organisation,
            'answered' => $answered,
            'norma' => $norma,
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function metrics(Request $request): Response
    {
        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        $norma = $organisation = null;
        if ($manager->isSingleMode()) {
            /** @var Norma */
            $norma = $manager->getActive();
            /** @var Builder */
            $query = Norma::whereKey($norma);
        } else {
            /** @var Organisation */
            $organisation = $manager->getActiveOrganisation();
            /** @var \App\Models\Auth\User $user */
            $user = $request->user();
            /** @var Builder */
            $query = Norma::active()->forOrganisation($organisation->id)->userHasAccess($user);
        }

        $query->forAssessMetrics()->orderBy('risk_rating', 'DESC');

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.assess.my.assessment-item-response.metrics',
            'target' => 'assess-metrics-dashboard',
            'baseQuery' => $query,
            'filters' => $request->all(),
        ]);

        return turboStreamResponse($view);
    }

    /**
     * @param Request $request
     *
     * @return View|RedirectResponse
     */
    public function index(Request $request): View|RedirectResponse
    {
        return $this->indexView($request, false);
    }

    /**
     * @param Request                $request
     * @param AssessmentItemResponse $response
     *
     * @return Response
     */
    public function answer(Request $request, AssessmentItemResponse $response): Response
    {
        $this->authorize('answer', $response);
        $this->validate($request, [
            'answer' => ['required', 'numeric'],
            'notes' => ['string', 'required', 'max:5000'],
        ]);

        /** @var User */
        $user = Auth::user();
        AnswerAssessmentItemResponse::run($response, $request->input(), $user);

        $response->refresh();
        $response->load(['assessmentItem.author']);

        /** @var View $view */
        $view = view('streams.assess.assessment-item-response.after-answer-stream', [
            'response' => $response,
        ]);

        return turboStreamResponse($view);
    }

    /**
     * @param AssessmentItemResponse $response
     * @param string                 $forAnswer
     *
     * @return Response
     */
    public function showAnswerForm(AssessmentItemResponse $response, string $forAnswer): Response
    {
        $answered = AssessmentItemResponse::where('assessment_item_id', $response->assessment_item_id)
            ->where('answer', '!=', ResponseStatus::draft()->value)
            ->exists();

        /** @var View */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.assess.my.assessment-item-response.answer-form',
            'target' => 'assessment-item-response-' . $response->id . '-answer-form-' . $forAnswer,
            'forAnswer' => $forAnswer === 'unchanged' ? $response->answer : $forAnswer,
            'response' => $response,
            'answered' => $answered,
        ]);

        return turboStreamResponse($view);
    }

    /**
     * Perform a given action on a given list of users.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function actions(Request $request): RedirectResponse
    {
        $actionName = $this->validateActionName($request);
        /** @var Collection<AssessmentItemResponse> */
        $aiResponses = $this->filterActionInput($request, AssessmentItemResponse::class);

        $response = $this->performAction($actionName, $aiResponses);

        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);

        /** @var \App\Models\Auth\User $user */
        $user = Auth::user();

        event(new BulkAnsweredAssessmentItems($user, $manager->getActive(), $manager->getActiveOrganisation()));

        return $response;
    }

    /**
     * @param string                             $action
     * @param Collection<AssessmentItemResponse> $aiResponses
     *
     * @return RedirectResponse
     */
    private function performAction(string $action, Collection $aiResponses): RedirectResponse
    {
        $responseIds = $aiResponses->modelKeys();
        switch ($action) {
            case 'respond-' . ResponseStatus::yes()->value:
                /** @var RedirectResponse $response */
                $response = redirect(route('my.assess.assessment-item-responses.bulk.answer'));
                Session::put(config('session-keys.assess.bulk-answer-ids'), $responseIds);
                Session::put(config('session-keys.assess.bulk-answer'), ResponseStatus::yes()->value);
                break;
            case 'respond-' . ResponseStatus::no()->value:
                /** @var RedirectResponse $response */
                $response = redirect(route('my.assess.assessment-item-responses.bulk.answer'));
                Session::put(config('session-keys.assess.bulk-answer-ids'), $responseIds);
                Session::put(config('session-keys.assess.bulk-answer'), ResponseStatus::no()->value);
                break;
            case 'respond-' . ResponseStatus::notApplicable()->value:
                /** @var RedirectResponse $response */
                $response = redirect(route('my.assess.assessment-item-responses.bulk.answer'));
                Session::put(config('session-keys.assess.bulk-answer-ids'), $responseIds);
                Session::put(config('session-keys.assess.bulk-answer'), ResponseStatus::notApplicable()->value);
                break;
            case 'respond-' . ResponseStatus::notAssessed()->value:
                /** @var RedirectResponse $response */
                $response = redirect(route('my.assess.assessment-item-responses.bulk.answer'));
                Session::put(config('session-keys.assess.bulk-answer-ids'), $responseIds);
                Session::put(config('session-keys.assess.bulk-answer'), ResponseStatus::notAssessed()->value);
                break;
            case 'respond-unchanged':
                /** @var RedirectResponse $response */
                $response = redirect(route('my.assess.assessment-item-responses.bulk.answer'));
                Session::put(config('session-keys.assess.bulk-answer-ids'), $responseIds);
                Session::put(config('session-keys.assess.bulk-answer'), 'unchanged');
                break;
            default:
                abort(422);
        }

        /** @var RedirectResponse */
        return $response;
    }

    /**
     * @return View
     */
    public function editBulkAnswer(): View
    {
        $answer = Session::get(config('session-keys.assess.bulk-answer'));
        if ($answer !== 'unchanged') {
            $answer = ResponseStatus::fromValue((int) $answer)->value;
        }
        $responseIds = Session::get(config('session-keys.assess.bulk-answer-ids'), []);

        /** @var View */
        return view('pages.assess.my.assessment-item-response.bulk-answer', [
            'responseIds' => $responseIds,
            'answer' => $answer,
            'answerText' => __('assess.assessment_item_response.respond_with.' . $answer),
        ]);
    }

    /**
     * @return RedirectResponse
     */
    public function bulkAnswerUpdate(Request $request): RedirectResponse
    {
        $this->validate($request, [
            'notes' => ['string', 'required', 'max:5000'],
        ]);

        $answer = Session::pull(config('session-keys.assess.bulk-answer'));
        if ($answer !== 'unchanged') {
            $answer = ResponseStatus::fromValue((int) $answer)->value;
        }
        $responseIds = Session::pull(config('session-keys.assess.bulk-answer-ids'), []);

        /** @var User */
        $user = Auth::user();
        /** @var Collection<AssessmentItemResponse> */
        $responses = AssessmentItemResponse::whereKey($responseIds)
            ->whereHas('norma', function ($q) use ($user) {
                $q->userHasAccess($user);
            })
            ->with(['norma', 'assessmentItem'])
            ->get();

        $draft = false;

        foreach ($responses as $aiResponse) {
            $draft = $draft || ResponseStatus::draft()->is($aiResponse->answer);
            $input = [
                'answer' => $answer !== 'unchanged' ? $answer : $aiResponse->answer,
                'notes' => $request->input('notes'),
            ];
            AnswerAssessmentItemResponse::run($aiResponse, $input, $user);
        }

        /** @var RedirectResponse */
        return $draft ? redirect(route('my.assess.pending')) : redirect(route('my.assess.assessment-item-responses.index'));
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function exportAsExcel(Request $request): Response
    {
        $filename = Str::random(15) . '.xlsx';

        /** @var ActiveNormasManager $manager */
        $manager = app(ActiveNormasManager::class);

        $filters = $request->all();

        /** @var Organisation $organisation */
        $organisation = $manager->getActiveOrganisation();
        $norma = $manager->getActive();
        /** @var \App\Models\Auth\User $user */
        $user = $request->user();
        $job = new GenerateAssessMetricsExportExcel($filename, $organisation, $norma, $user, $filters);
        $this->dispatch($job);

        event(new GenericActivity(
            $user,
            UserActivityType::exportedConsolidatedPerformanceReport(),
            null,
            $norma,
            $organisation
        ));

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.system.file-download-progress',
            'target' => 'download-progress',
            'jobId' => $job->getJobStatusId(),
            'percentage' => 0,
            'redirect' => route('my.downloads.download.assess.metrics.excel', ['filename' => $filename], false),
        ]);

        return turboStreamResponse($view);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function exportResponsesAsExcel(Request $request): Response
    {
        $filename = Str::random(15) . '.xlsx';

        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);

        $filters = $request->all();

        $norma = $manager->getActive();

        /** @var Organisation */
        $organisation = $manager->getActiveOrganisation();
        /** @var \App\Models\Auth\User $user */
        $user = $request->user();
        $job = new GenerateAssessResponsesExportExcel($filename, $user, $organisation, $norma, $filters);
        $this->dispatch($job);

        event(new ExportedAssessmentItems($user, $norma, $organisation));

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.system.file-download-progress',
            'target' => 'download-progress',
            'jobId' => $job->getJobStatusId(),
            'percentage' => 0,
            'redirect' => route('my.downloads.download.assess.responses.excel', ['filename' => $filename], false),
        ]);

        return turboStreamResponse($view);
    }

    /**
     * Get the detail table as a response.
     *
     * @param AssessmentItemResponse $response
     *
     * @return Response
     */
    protected function detailTableResponse(AssessmentItemResponse $response): Response
    {
        $response->load(['assessmentItem.author', 'lastAnsweredBy']);

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.assess.my.assessment-item-response.detail-table',
            'target' => "response_{$response->hash_id}_table",
            'response' => $response,
        ]);

        return turboStreamResponse($view);
    }

    /**
     * Update the frequency of the response.
     *
     * @param Request $request
     * @param string  $response
     *
     * @return Response
     */
    public function updateFrequency(Request $request, string $response): Response
    {
        $response = $this->decodeHash($response, AssessmentItemResponse::class);
        /** @var AssessmentItemResponse $response */
        $response->updateQuietly($request->only(['frequency', 'frequency_interval']));

        return $this->detailTableResponse($response);
    }

    /**
     * Update the frequency of the response.
     *
     * @param Request $request
     * @param string  $response
     *
     * @return Response
     */
    public function updateNextDue(Request $request, string $response): Response
    {
        $response = $this->decodeHash($response, AssessmentItemResponse::class);
        /** @var AssessmentItemResponse $response */
        $response->updateQuietly($request->only(['next_due_at']));

        return $this->detailTableResponse($response);
    }
}
