<?php

namespace App\Http\Controllers\Support;

use App\Enums\Auth\UserActivityType;
use App\Events\Auth\UserActivity\GenericActivity;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\GetsNormaAndOrganisation;
use App\Models\Auth\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HelpController extends Controller
{
    use GetsNormaAndOrganisation;

    public function searchPortal(Request $request): RedirectResponse
    {
        /** @var string */
        $term = $request->input('term', '');

        /** @var User */
        $user = Auth::user();
        [$norma, $organisation] = $this->getActiveNormaAndOrganisation();
        GenericActivity::dispatch($user, UserActivityType::knowledgebaseSearch(), ['term' => $term], $norma, $organisation);

        /** @var RedirectResponse */
        return redirect('https://success.norma.com/en/knowledge/kb-search-results?term=' . $term);
    }
}
