<?php

namespace App\Http\Controllers\Assess\My\Settings;

use App\Actions\Assess\AssessmentItemResponse\CreateResponsesForOrganisation;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PerformsActions;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Customer\Norma;
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
     * @param Norma                    $norma
     *
     * @return View
     */
    public function setupForNorma(ActiveOrganisationManager $activeOrganisationManager, Norma $norma): View
    {
        /** @var View */
        return view('pages.assess.my.assessment-item.settings.setup', [
            'norma' => $norma,
            'organisation' => $activeOrganisationManager->getActive(),
        ]);
    }

    /**
     * @param Norma $norma
     *
     * @return Response
     */
    public function unusedItemsForNorma(Norma $norma): Response
    {
        $baseQuery = AssessmentItem::possibleForNorma($norma)
            ->noResponsesForNorma($norma);

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.assess.my.assessment-item.settings.unused-items',
            'target' => 'settings-assess-setup-for-norma-unused-items-' . $norma->id,
            'norma' => $norma,
            'baseQuery' => $baseQuery,
            'count' => $baseQuery->count(),
        ]);

        return turboStreamResponse($view);
    }

    /**
     * @param Norma $norma
     *
     * @return Response
     */
    public function usedItemsForNorma(Norma $norma): Response
    {
        $baseQuery = AssessmentItemResponse::forNorma($norma)
            // we also want to check soft deleted AssessmentItems
            ->whereNotIn('assessment_item_id', AssessmentItem::possibleForNorma($norma)->select('id'))
            ->with(['assessmentItem']);

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.assess.my.assessment-item.settings.used-items',
            'target' => 'settings-assess-setup-for-norma-used-items-' . $norma->id,
            'norma' => $norma,
            'baseQuery' => $baseQuery,
            'count' => $baseQuery->count(),
        ]);

        return turboStreamResponse($view);
    }

    /**
     * @param Request $request
     * @param Norma  $norma
     *
     * @return RedirectResponse
     */
    public function actionsForNorma(Request $request, Norma $norma): RedirectResponse
    {
        $actionName = $this->validateActionName($request);

        if ($actionName === 'add_unused_items') {
            /** @var \Illuminate\Database\Eloquent\Collection<AssessmentItem> */
            $collection = $this->filterActionInput($request, AssessmentItem::class);
        } else {
            /** @var \Illuminate\Database\Eloquent\Collection<AssessmentItemResponse> */
            $collection = $this->filterActionInput($request, AssessmentItemResponse::class);
        }

        $redirectRoute = $this->performActionForNorma($actionName, $norma, $collection);

        /** @var RedirectResponse */
        return redirect($redirectRoute);
    }

    /**
     * @param string                                            $action
     * @param Norma                                            $norma
     * @param Collection<AssessmentItem|AssessmentItemResponse> $collection
     *
     * @return string
     */
    private function performActionForNorma(string $action, Norma $norma, Collection $collection): string
    {
        switch ($action) {
            case 'add_unused_items':
                /** @var Collection<AssessmentItem> $collection */
                $this->assessmentItemResponseStore->createResponsesForItems($collection, $norma);
                $redirectRoute = route('my.settings.assess.setup.unused.items.for.norma', ['norma' => $norma]);
                break;
            case 'remove_used_items':
                /** @var Collection<AssessmentItemResponse> $collection */
                $this->assessmentItemResponseStore->removeResponses($collection, $norma);
                $redirectRoute = route('my.settings.assess.setup.used.items.for.norma', ['norma' => $norma]);
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
            ->noResponsesForNormasInOrganisation($organisation);

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
