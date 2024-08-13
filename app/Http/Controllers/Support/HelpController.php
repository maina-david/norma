<?php

namespace App\Http\Controllers\Support;

use App\Enums\Auth\UserActivityType;
use App\Events\Auth\UserActivity\GenericActivity;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\GetsLibryoAndOrganisation;
use App\Models\Auth\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HelpController extends Controller
{
    use GetsLibryoAndOrganisation;

    public function searchPortal(Request $request): RedirectResponse
    {
        /** @var string */
        $term = $request->input('term', '');

        /** @var User */
        $user = Auth::user();
        [$libryo, $organisation] = $this->getActiveLibryoAndOrganisation();
        GenericActivity::dispatch($user, UserActivityType::knowledgebaseSearch(), ['term' => $term], $libryo, $organisation);

        /** @var RedirectResponse */
        return redirect('https://success.libryo.com/en/knowledge/kb-search-results?term=' . $term);
    }
}
