<?php

namespace App\Http\Controllers\Auth\My;

use App\Http\Controllers\Controller;
use App\Services\Auth\ImpersonationManager;
use Illuminate\Http\RedirectResponse;

class UserController extends Controller
{
    /**
     * @param ImpersonationManager $impersonationManager
     */
    public function __construct(
        protected ImpersonationManager $impersonationManager
    ) {
    }

    /**
     * @return RedirectResponse
     */
    public function leaveImpersonation(): RedirectResponse
    {
        $this->impersonationManager->leave();

        return redirect()->route('my.dashboard');
    }
}
