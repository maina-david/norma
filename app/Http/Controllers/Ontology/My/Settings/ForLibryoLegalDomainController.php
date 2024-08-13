<?php

namespace App\Http\Controllers\Ontology\My\Settings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PerformsActions;
use App\Models\Customer\Libryo;
use App\Models\Ontology\LegalDomain;
use App\Services\Customer\ActiveOrganisationManager;
use App\Stores\Customer\LibryoLegalDomainStore;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ForLibryoLegalDomainController extends Controller
{
    use PerformsActions;

    /**
     * @param ActiveOrganisationManager $activeOrganisationManager
     * @param Libryo                    $libryo
     *
     * @return View
     */
    public function index(ActiveOrganisationManager $activeOrganisationManager, Libryo $libryo): View
    {
        /** @var View */
        return view('pages.ontology.my.legal-domain.settings.for-libryo', [
            'baseQuery' => LegalDomain::whereRelation('libryos', 'id', $libryo->id),
            'libryo' => $libryo,
            'organisation' => $activeOrganisationManager->getActive(),
        ]);
    }

    /**
     * Perform a given action on a given list of users.
     *
     * @param Request $request
     * @param Libryo  $libryo
     *
     * @return RedirectResponse
     */
    public function actions(Request $request, Libryo $libryo): RedirectResponse
    {
        $actionName = $this->validateActionName($request);

        /** @var Collection<int, LegalDomain> $domains */
        $domains = $this->filterActionInput($request, LegalDomain::class);

        $flashMessage = $this->performAction($actionName, $libryo, $domains);

        Session::flash('flash.message', $flashMessage);

        /** @var RedirectResponse */
        return redirect(route('my.settings.libryos.compilation.legal-domains.index', ['libryo' => $libryo->id]));
    }

    /**
     * Trigger the action.
     *
     * @param string                       $action
     * @param Libryo                       $libryo
     * @param Collection<int, LegalDomain> $domains
     *
     * @return string
     */
    private function performAction(string $action, Libryo $libryo, Collection $domains): string
    {
        $store = app(LibryoLegalDomainStore::class);

        switch ($action) {
            case 'remove_from_libryo':
                $store->detachLegalDomains($libryo, $domains);
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
     * Attach the legal domains to the libryo.
     *
     * @param Request $request
     * @param Libryo  $libryo
     *
     * @return RedirectResponse
     */
    public function add(Request $request, Libryo $libryo): RedirectResponse
    {
        $domains = LegalDomain::whereKey($request->input('legal-domains', []))->get();

        app(LibryoLegalDomainStore::class)->attachLegalDomains($libryo, $domains);

        Session::flash('flash.message', __('actions.success'));

        /** @var RedirectResponse */
        return redirect(route('my.settings.libryos.compilation.legal-domains.index', ['libryo' => $libryo->id]));
    }
}
