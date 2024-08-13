<?php

namespace App\Traits\Actions;

use App\Models\Customer\Libryo;
use Illuminate\Support\Facades\Session;

trait UsesActionAreasInLibryo
{
    /**
     * Redirect if the module is not enabled.
     *
     * @param \App\Models\Customer\Libryo|null $libryo
     *
     * @return void
     */
    public function redirectIfNoActionAreas(?Libryo $libryo): void
    {
        if ($libryo && !$libryo->hasActionsModule()) {
            Session::flash('flash.type', 'error');
            Session::flash('flash.message', __('actions.action_area.not_enabled'));

            abort(redirect()->route('my.dashboard'));
        }
    }
}
