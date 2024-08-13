<?php

namespace App\Http\Controllers\Api\Internals\My\Auth;

use App\Http\Resources\Internals\My\Auth\UserResource;
use App\Models\Auth\User;
use App\Services\Customer\ActiveLibryosManager;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class OrganisationUsersController
{
    /**
     * Get the organisation users.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var ActiveLibryosManager $manager */
        $manager = app(ActiveLibryosManager::class);
        $organisation = $manager->getActiveOrganisation();

        $query = $organisation->users();

        if ($request->has('libryo') && $manager->isSingleMode() && $libryo = $manager->getActive()) {
            $query = User::libryoAccess($libryo)->inOrganisation($organisation->id);
        }

        $users = $query->orderBy('fname')
            ->active()
            ->get(['id', 'sname', 'fname', 'avatar_attachment_id'])
            ->keyBy('id');

        /** @var int $userId */
        $userId = Auth::id();

        if (!$users->get($userId)) {
            /** @var User $user */
            $user = $request->user();
            $users->prepend($user, $userId);
        }

        return UserResource::collection($users->values());
    }
}
