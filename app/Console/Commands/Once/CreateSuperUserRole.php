<?php

namespace App\Console\Commands\Once;

use App\Enums\Application\ApplicationType;
use App\Models\Auth\Role;
use Illuminate\Console\Command;

class CreateSuperUserRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles:create-superusers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create roles with all permissions.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Role::create([
            'title' => 'Admin Super Users',
            'app' => ApplicationType::admin()->value,
            'permissions' => [config('permissions.superuser-name')],
        ]);

        Role::create([
            'title' => 'Collaborate Super Users',
            'app' => ApplicationType::collaborate()->value,
            'permissions' => [config('permissions.superuser-name')],
        ]);

        Role::create([
            'title' => 'My Super Users',
            'app' => ApplicationType::my()->value,
            'permissions' => [config('permissions.superuser-name')],
        ]);

        $this->info('Done!');

        return 0;
    }
}
