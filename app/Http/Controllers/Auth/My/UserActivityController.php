<?php

namespace App\Http\Controllers\Auth\My;

use App\Enums\Auth\UserActivityType;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\GetsLibryoAndOrganisation;
use App\Models\Auth\User;
use App\Repositories\Auth\UserActivityRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserActivityController extends Controller
{
    use GetsLibryoAndOrganisation;

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

        [$libryo, $organisation] = $this->getActiveLibryoAndOrganisation();
        $this->userActivityRepository->addActivity($user, $type, $details, $libryo, $organisation);

        return response()->json([]);
    }
}
