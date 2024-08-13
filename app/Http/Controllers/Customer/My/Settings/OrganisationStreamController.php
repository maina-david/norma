<?php

namespace App\Http\Controllers\Customer\My\Settings;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class OrganisationStreamController extends Controller
{
    public function switcherSearch(Request $request): Response
    {
        $searchStr = $request->input('search_organisations');
        /** @var User $user */
        $user = Auth::user();

        if ($user->can('my.settings.customer.organisation.viewAny') || $user->isMySuperUser()) {
            $q = (new Organisation())->newQuery();
        } else {
            $q = Organisation::userIsOrganisationAdmin($user);
        }

        $items = $q->where('title', 'LIKE', '%' . $searchStr . '%')
            ->limit(200)
            ->pluck('title', 'id')
            ->toArray();

        return turboStreamResponse(view('streams.customer.organisation.switcher-search-results', ['listItems' => $items]));
    }
}
