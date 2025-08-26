<?php

namespace App\Http\Controllers\Auth\My;

use App\Enums\Auth\UserActivityType;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\GetsNormaAndOrganisation;
use App\Models\Auth\User;
use App\Repositories\Auth\UserActivityRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserActivityController extends Controller
{
    use GetsNormaAndOrganisation;

    public function __construct(protected UserActivityRepository $userActivityRepository)
    {
    }

    /**
     * Used to track client side events.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function trackEvent(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var UserActivityType */
        $type = UserActivityType::fromValue($request->input('type'));
        $details = $request->input('details', false);

        if ($details !== false) {
            $details = json_encode($details);
        }

        [$norma, $organisation] = $this->getActiveNormaAndOrganisation();
        $this->userActivityRepository->addActivity($user, $type, $details, $norma, $organisation);

        return response()->json([]);
    }
}
