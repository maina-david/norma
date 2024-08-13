<?php

namespace App\Http\Controllers\Assess\My\Settings;

use App\Actions\Assess\AssessmentItemResponse\CreateResponsesForOrganisation;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PerformsActions;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Services\Customer\ActiveOrganisationManager;
use App\Stores\Assess\AssessmentItemResponseStore;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;

class AssessSetupStreamController extends Controller
{
    use PerformsActions;

    public function __construct(protected AssessmentItemResponseStore $assessmentItemResponseStore)
    {
    }

    /**
     * @param ActiveOrganisationManager $activeOrganisationManager
     * @param Libryo                    $libryo
     *
     * @return View
     */
    public function setupForLibryo(ActiveOrganisationManager $activeOrganisationManager, Libryo $libryo): View
    {
        /** @var View */
        return view('pages.assess.my.assessment-item.settings.setup', [
            'libryo' => $libryo,
            'organisation' => $activeOrganisationManager->getActive(),
        ]);
    }

    /**
     * @param Libryo $libryo
     *
     * @return Response
     */
    public function unusedItemsForLibryo(Libryo $libryo): Response
    {
        $baseQuery = AssessmentItem::possibleForLibryo($libryo)
            ->noResponsesForLibryo($libryo);

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.assess.my.assessment-item.settings.unused-items',
            'target' => 'settings-assess-setup-for-libryo-unused-items-' . $libryo->id,
            'libryo' => $libryo,
            'baseQuery' => $baseQuery,
            'count' => $baseQuery->count(),
        ]);

        return turboStreamResponse($view);
    }

    /**
     * @param Libryo $libryo
     *
     * @return Response
     */
    public function usedItemsForLibryo(Libryo $libryo): Response
    {
        $baseQuery = AssessmentItemResponse::forLibryo($libryo)
            // we also want to check soft deleted AssessmentItems
            ->whereNotIn('assessment_item_id', AssessmentItem::possibleForLibryo($libryo)->select('id'))
            ->with(['assessmentItem']);

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.assess.my.assessment-item.settings.used-items',
            'target' => 'settings-assess-setup-for-libryo-used-items-' . $libryo->id,
            'libryo' => $libryo,
            'baseQuery' => $baseQuery,
            'count' => $baseQuery->count(),
        ]);

        return turboStreamResponse($view);
    }

    /**
     * @param Request $request
     * @param Libryo  $libryo
     *
     * @return RedirectResponse
     */
    public function actionsForLibryo(Request $request, Libryo $libryo): RedirectResponse
    {
        $actionName = $this->validateActionName($request);

        if ($actionName === 'add_unused_items') {
            /** @var \Illuminate\Database\Eloquent\Collection<AssessmentItem> */
            $collection = $this->filterActionInput($request, AssessmentItem::class);
        } else {
            /** @var \Illuminate\Database\Eloquent\Collection<AssessmentItemResponse> */
            $collection = $this->filterActionInput($request, AssessmentItemResponse::class);
        }

        $redirectRoute = $this->performActionForLibryo($actionName, $libryo, $collection);

        /** @var RedirectResponse */
        return redirect($redirectRoute);
    }

    /**
     * @param string                                            $action
     * @param Libryo                                            $libryo
     * @param Collection<AssessmentItem|AssessmentItemResponse> $collection
     *
     * @return string
     */
    private function performActionForLibryo(string $action, Libryo $libryo, Collection $collection): string
    {
        switch ($action) {
            case 'add_unused_items':
                /** @var Collection<AssessmentItem> $collection */
                $this->assessmentItemResponseStore->createResponsesForItems($collection, $libryo);
                $redirectRoute = route('my.settings.assess.setup.unused.items.for.libryo', ['libryo' => $libryo]);
                break;
            case 'remove_used_items':
                /** @var Collection<AssessmentItemResponse> $collection */
                $this->assessmentItemResponseStore->removeResponses($collection, $libryo);
                $redirectRoute = route('my.settings.assess.setup.used.items.for.libryo', ['libryo' => $libryo]);
                break;
                // @codeCoverageIgnoreStart
            default:
                abort(422);
                // @codeCoverageIgnoreEnd
        }

        return $redirectRoute;
    }

    /**
     * @param Organisation $organisation
     *
     * @return Response
     */
    public function setupForOrganisation(Organisation $organisation): Response
    {
        $baseQuery = AssessmentItem::possibleForOrganisation($organisation)
            ->noResponsesForLibryosInOrganisation($organisation);

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.assess.my.assessment-item.settings.setup-organisation',
            'target' => 'settings-assess-setup-for-organisation-' . $organisation->id,
            'organisation' => $organisation,
            'count' => $baseQuery->count(),
        ]);

        return turboStreamResponse($view);
    }

    /**
     * @param Organisation $organisation
     *
     * @return RedirectResponse
     */
    public function activateUnusedItemsForOrganisation(Organisation $organisation): RedirectResponse
    {
        CreateResponsesForOrganisation::dispatch($organisation);
        /** @var string */
        $message = __('assess.setup.activation_being_processed');
        Session::flash('flash.message', $message);

        return back();
    }
}
