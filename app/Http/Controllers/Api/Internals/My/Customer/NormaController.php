<?php

namespace App\Http\Controllers\Api\Internals\My\Customer;

use App\Http\Resources\Internals\My\Customer\NormaResource;
use App\Services\Customer\ActiveNormasManager;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class NormaController
{
    /**
     * Get the normas that belong to the current organisation.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(ActiveNormasManager $manager): AnonymousResourceCollection
    {
        /** @var \App\Models\Auth\User $user */
        $user = Auth::user();

        $normas = $manager->getActiveOrganisation()
            ->normas()
            ->active()
            ->userHasAccess($user)
            ->get(['id', 'title']);

        return NormaResource::collection($normas);
    }
}
