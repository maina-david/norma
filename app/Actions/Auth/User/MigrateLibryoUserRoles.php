<?php

namespace App\Actions\Auth\User;

use App\Models\Auth\Role;
use App\Models\Auth\User;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Just a temporary job that we'll run  while we change from the old codebase to the
 * monolith. Can be removed once all code bases are on monolith.
 *
 * @codeCoverageIgnore
 */
class MigrateLibryoUserRoles
{
    use AsAction;

    public string $commandSignature = 'libryo:migrate-libryo-user-roles';

    public function handle(): void
    {
        $legacyRoles = Role::where('app', 'libryo')->where('title', '!=', 'Turks Capturer')->get();
        $cursor = User::whereIn('role_id', $legacyRoles->modelKeys())
            ->doesntHave('roles')
            ->cursor();
        foreach ($cursor as $user) {
            switch ($user->role_id) {
                // Administators
                case 101:
                    DB::table('user_role')->insertOrIgnore(['user_id' => $user->id, 'role_id' => 127]);
                    break;
                    // Users
                case 113:
                    DB::table('user_role')->insertOrIgnore(['user_id' => $user->id, 'role_id' => 131]);
                    break;
                    // Integration Users
                case 118:
                    DB::table('user_role')->insertOrIgnore(['user_id' => $user->id, 'role_id' => 132]);
                    break;
            }
        }
    }

    public function asJob(): void
    {
        $this->handle();
    }

    public function asCommand(): void
    {
        $this->handle();
    }
}
