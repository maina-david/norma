<?php

namespace App\Events\Auth\UserActivity\Projects;

use App\Enums\Auth\UserActivityType;
use App\Events\Auth\UserActivity\UserActivityEvent;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Tasks\TaskProject;

class ProjectCreated extends UserActivityEvent
{
    /**
     * @param \App\Models\Tasks\TaskProject          $project
     * @param \App\Models\Auth\User                  $user
     * @param \App\Models\Customer\Libryo|null       $libryo
     * @param \App\Models\Customer\Organisation|null $organisation
     */
    public function __construct(protected TaskProject $project, User $user, ?Libryo $libryo = null, ?Organisation $organisation = null)
    {
        parent::__construct($user, $libryo, $organisation);
    }

    /**
     * {@inheritDoc}
     */
    public function getActivityType(): UserActivityType
    {
        return UserActivityType::createdProject();
    }

    /**
     * {@inheritDoc}
     */
    public function toJson(int $options = 0): string|false
    {
        return json_encode([
            'project' => [
                'id' => $this->project->id,
            ],
        ], $options);
    }
}
