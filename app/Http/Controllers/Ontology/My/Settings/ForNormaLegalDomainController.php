<?php

namespace App\Http\Controllers\Ontology\My\Settings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PerformsActions;
use App\Models\Customer\Norma;
use App\Models\Ontology\LegalDomain;
use App\Services\Customer\ActiveOrganisationManager;
use App\Stores\Customer\NormaLegalDomainStore;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ForNormaLegalDomainController extends Controller
{
    use PerformsActions;

    /**
     * @param ActiveOrganisationManager $activeOrganisationManager
     * @param Norma                    $norma
     *
     * @return View
     */
    public function index(ActiveOrganisationManager $activeOrganisationManager, Norma $norma): View
    {
        /** @var View */
        return view('pages.ontology.my.legal-domain.settings.for-norma', [
            'baseQuery' => LegalDomain::whereRelation('normas', 'id', $norma->id),
            'norma' => $norma,
            'organisation' => $activeOrganisationManager->getActive(),
        ]);
    }

    /**
     * Perform a given action on a given list of users.
     *
     * @param Request $request
     * @param Norma  $norma
     *
     * @return RedirectResponse
     */
    public function actions(Request $request, Norma $norma): RedirectResponse
    {
        $actionName = $this->validateActionName($request);

        /** @var Collection<int, LegalDomain> $domains */
        $domains = $this->filterActionInput($request, LegalDomain::class);

        $flashMessage = $this->performAction($actionName, $norma, $domains);

        Session::flash('flash.message', $flashMessage);

        /** @var RedirectResponse */
        return redirect(route('my.settings.normas.compilation.legal-domains.index', ['norma' => $norma->id]));
    }

    /**
     * Trigger the action.
     *
     * @param string                       $action
     * @param Norma                       $norma
     * @param Collection<int, LegalDomain> $domains
     *
     * @return string
     */
    private function performAction(string $action, Norma $norma, Collection $domains): string
    {
        $store = app(NormaLegalDomainStore::class);

        switch ($action) {
            case 'remove_from_norma':
                $store->detachLegalDomains($norma, $domains);
                break;

                // @codeCoverageIgnoreStart
            default:
                abort(422);
                // @codeCoverageIgnoreEnd
        }

        /** @var string */
        return __('actions.success');
    }

    /**
     * Attach the legal domains to the norma.
     *
     * @param Request $request
     * @param Norma  $norma
     *
     * @return RedirectResponse
     */
    public function add(Request $request, Norma $norma): RedirectResponse
    {
        $domains = LegalDomain::whereKey($request->input('legal-domains', []))->get();

        app(NormaLegalDomainStore::class)->attachLegalDomains($norma, $domains);

        Session::flash('flash.message', __('actions.success'));

        /** @var RedirectResponse */
        return redirect(route('my.settings.normas.compilation.legal-domains.index', ['norma' => $norma->id]));
    }
}
