<?php

namespace App\Traits\Actions;

use App\Models\Customer\Norma;
use Illuminate\Support\Facades\Session;

trait UsesActionAreasInNorma
{
    /**
     * Redirect if the module is not enabled.
     *
     * @param \App\Models\Customer\Norma|null $norma
     *
     * @return void
     */
    public function redirectIfNoActionAreas(?Norma $norma): void
    {
        if ($norma && !$norma->hasActionsModule()) {
            Session::flash('flash.type', 'error');
            Session::flash('flash.message', __('actions.action_area.not_enabled'));

            abort(redirect()->route('my.dashboard'));
        }
    }
}
