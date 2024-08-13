<?php

namespace Database\Seeders\Development;

use App\Enums\Application\ApplicationType;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Auth\UserRole;
use App\Models\Collaborators\Profile;
use App\Models\Customer\Pivots\LibryoUser;
use App\Models\Customer\Pivots\TeamUser;
use App\Models\Customer\Team;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        Role::truncate();
        User::truncate();
        Profile::truncate();
        Team::truncate();
        UserRole::truncate();
        TeamUser::truncate();
        LibryoUser::truncate();

        $mySuper = Role::create([
            'title' => 'My Super Users',
            'app' => ApplicationType::my()->value,
            'permissions' => [config('permissions.superuser-name')],
        ]);

        $collaborateSuper = Role::create([
            'title' => 'Collaborate Super Users',
            'app' => ApplicationType::collaborate()->value,
            'permissions' => [config('permissions.superuser-name')],
        ]);

        $user = User::factory()->create(['email' => '1@example.com']);
        Profile::factory()->create(['turk_application_id' => null, 'user_id' => $user->id]);
        $user->roles()->attach([$mySuper->id, $collaborateSuper->id]);

        $team = Team::create(['organisation_id' => 1, 'title' => 'Team Libryo']);

        $user->teams()->attach($team->id);
        $user->libryos()->attach([1, 2, 3]);
    }
}
