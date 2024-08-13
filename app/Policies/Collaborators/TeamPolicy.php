<?php

namespace App\Policies\Collaborators;

use App\Models\Auth\User;
use App\Models\Collaborators\Profile;
use App\Models\Collaborators\Team;
use Illuminate\Auth\Access\HandlesAuthorization;

class TeamPolicy
{
    use HandlesAuthorization;

    /**
     * Check if the user can manage the team.
     *
     * @param User $user
     *
     * @return bool
     */
    public function manage(User $user): bool
    {
        return Profile::where('user_id', $user->id)->where('is_team_admin', true)->exists() || $user->can('collaborate.teams.view');
    }

    /**
     * Check if the user can view the payments.
     *
     * @param User $user
     * @param Team $team
     *
     * @return bool
     */
    public function viewPayments(User $user, Team $team): bool
    {
        /** @var Profile|null $profile */
        $profile = Profile::where('user_id', $user->id)->first();

        return $user->can('collaborate.collaborators.team.view') || ($profile && $profile->is_team_admin && $profile->team_id === $team->id);
    }
}
