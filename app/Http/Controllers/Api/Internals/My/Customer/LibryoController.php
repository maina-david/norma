<?php

namespace App\Http\Controllers\Api\Internals\My\Customer;

use App\Http\Resources\Internals\My\Customer\LibryoResource;
use App\Services\Customer\ActiveLibryosManager;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class LibryoController
{
    /**
     * Get the libryos that belong to the current organisation.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(ActiveLibryosManager $manager): AnonymousResourceCollection
    {
        /** @var \App\Models\Auth\User $user */
        $user = Auth::user();

        $libryos = $manager->getActiveOrganisation()
            ->libryos()
            ->active()
            ->userHasAccess($user)
            ->get(['id', 'title']);

        return LibryoResource::collection($libryos);
    }
}
