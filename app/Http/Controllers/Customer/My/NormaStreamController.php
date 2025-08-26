<?php

namespace App\Http\Controllers\Customer\My;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Notify\LegalUpdate;
use App\Services\Customer\ActiveNormasManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class NormaStreamController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function switcherSearch(Request $request): Response
    {
        /** @var User */
        $user = Auth::user();
        $searchStr = $request->input('search_streams');
        $items = Norma::where('title', 'LIKE', '%' . $searchStr . '%')
            ->active()
            ->userHasAccess($user)
            ->limit(200)
            ->pluck('title', 'id')
            ->toArray();

        return turboStreamResponse(view('streams.customer.norma.switcher-search-results', ['listItems' => $items]));
    }

    /**
     * @return Response
     */
    public function switcherRecent(): Response
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        $normas = $manager->get($user, 5);

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.customer.my.norma.switcher-list',
            'target' => 'norma-switcher-recent-list',
            'listItems' => $normas->pluck('title', 'id')->toArray(),
        ]);

        return turboStreamResponse($view);
    }

    public function applicableForLegalUpdate(Request $request, LegalUpdate $update): Response
    {
        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        /** @var Organisation */
        $organisation = $manager->getActiveOrganisation();
        /** @var \App\Models\Auth\User $user */
        $user = $request->user();

        $query = Norma::forUpdate($update)
            ->forOrganisation($organisation->id)
            ->userHasAccess($user)
            ->active();

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.customer.my.norma.update-applicable-to',
            'target' => 'update-applicable-streams-' . $update->id,
            'baseQuery' => $query,
            'update' => $update,
        ]);

        return turboStreamResponse($view);
    }
}
