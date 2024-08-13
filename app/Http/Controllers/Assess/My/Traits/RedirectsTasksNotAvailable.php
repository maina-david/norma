<?php

namespace App\Http\Controllers\Assess\My\Traits;

use App\Models\Customer\Libryo;
use Illuminate\Support\Facades\Session;

trait RedirectsTasksNotAvailable
{
    /**
     * Redirect if the module is not enabled.
     *
     * @codeCoverageIgnore
     *
     * @param \App\Models\Customer\Libryo|null $libryo
     *
     * @return void
     */
    public function redirectIfNoTasks(?Libryo $libryo): void
    {
        if ($libryo && !$libryo->hasTasksModule()) {
            Session::flash('flash.type', 'error');
            Session::flash('flash.message', __('tasks.tasks_not_enabled'));

            abort(redirect()->route('my.dashboard'));
        }
    }
}
