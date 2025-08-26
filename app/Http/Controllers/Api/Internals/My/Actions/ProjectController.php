<?php

namespace App\Http\Controllers\Api\Internals\My\Actions;

use App\Http\Resources\Internals\My\Tasks\TaskProjectResource;
use App\Services\Customer\ActiveNormasManager;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectController
{
    /**
     * Get the organisation's project.
     *
     * @param \App\Services\Customer\ActiveNormasManager $manager
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(ActiveNormasManager $manager): AnonymousResourceCollection
    {
        $organisation = $manager->getActiveOrganisation();
        $projects = $organisation->taskProjects()->active()->get(['id', 'title']);

        return TaskProjectResource::collection($projects);
    }
}
