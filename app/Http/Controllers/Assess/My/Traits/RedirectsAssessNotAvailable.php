<?php

namespace App\Http\Controllers\Assess\My\Traits;

use App\Models\Customer\Libryo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;

trait RedirectsAssessNotAvailable
{
    public function redirectIfNoAssess(Libryo $libryo): ?RedirectResponse
    {
        if (!$libryo->hasAssessModule()) {
            Session::flash('flash.type', 'error');
            Session::flash('flash.message', __('assess.assess_not_enabled'));

            return redirect()->route('my.dashboard');
        }

        return null;
    }
}
