<?php

namespace App\Http\Controllers\Collaborators\Collaborate;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Collaborators\Collaborator;
use App\Services\Auth\ImpersonationManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class CollaboratorImpersonationController extends Controller
{
    /**
     * Impersonate the given collaborator.
     *
     * @param ImpersonationManager $impersonationManager
     * @param Collaborator         $collaborator
     *
     * @return RedirectResponse
     */
    public function store(ImpersonationManager $impersonationManager, Collaborator $collaborator)
    {
        /** @var User */
        $currentUser = Auth::user();

        $impersonationManager->take($currentUser, $collaborator);

        return redirect()->route('collaborate.dashboard');
    }

    /**
     * Leave the impersonation session.
     *
     * @param ImpersonationManager $impersonationManager
     *
     * @return RedirectResponse
     */
    public function destroy(ImpersonationManager $impersonationManager)
    {
        $impersonationManager->leave();

        return redirect()->route('collaborate.dashboard');
    }
}
