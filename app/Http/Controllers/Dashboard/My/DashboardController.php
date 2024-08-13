<?php

namespace App\Http\Controllers\Dashboard\My;

use App\Enums\Compilation\ContextQuestionAnswer;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Customer\Pivots\ContextQuestionLibryo;
use App\Services\Customer\ActiveLibryosManager;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function dashboard(): View
    {
        /** @var User */
        $user = Auth::user();

        $manager = app(ActiveLibryosManager::class);
        $libryo = $manager->getActive();

        $pendingApplicability = 0;

        if ($manager->isSingleMode() && $user->canManageApplicability() && $libryo?->auto_compiled) {
            $pendingApplicability = ContextQuestionLibryo::where('place_id', $libryo->id)
                ->where('answer', ContextQuestionAnswer::maybe()->value)
                ->count();
        }

        /** @var View */
        return view('pages.dashboard.my.dashboard', [
            'user' => $user,
            'unreadNotificationsCount' => $user->unreadNotifications()->count(),
            'pendingApplicability' => $pendingApplicability,
        ]);
    }
}
