<?php

namespace App\Console\Commands\Auth;

use App\Models\Auth\Role;
use App\Models\Auth\User;
use Illuminate\Console\Command;

class Verify2FA extends Command
{
    protected $signature = 'auth:disable-non-2fa-admins';
    protected $description = 'Disables admin accounts that do not have 2FA enabled';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $superuserRole = Role::whereJsonContains('permissions', config('permissions.superuser-name'))
            ->get()
            ->pluck('id')->toArray();

        User::where('active', true)
            ->whereNull('two_factor_secret')
            ->whereNull('two_factor_recovery_codes')
            ->whereHas('roles', function ($query) use ($superuserRole) {
                $query->whereIn('id', $superuserRole);
            })
            ->update(['active' => false]);

        return 0;
    }
}
