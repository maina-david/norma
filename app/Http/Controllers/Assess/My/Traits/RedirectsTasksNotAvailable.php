<?php

namespace App\Http\Controllers\Assess\My\Traits;

use App\Models\Customer\Norma;
use Illuminate\Support\Facades\Session;

trait RedirectsTasksNotAvailable
{
    /**
     * Redirect if the module is not enabled.
     *
     * @codeCoverageIgnore
     *
     * @param \App\Models\Customer\Norma|null $norma
     *
     * @return void
     */
    public function redirectIfNoTasks(?Norma $norma): void
    {
        if ($norma && !$norma->hasTasksModule()) {
            Session::flash('flash.type', 'error');
            Session::flash('flash.message', __('tasks.tasks_not_enabled'));

            abort(redirect()->route('my.dashboard'));
        }
    }
}
