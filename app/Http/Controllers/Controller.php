<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Session;

abstract class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    /**
     * Notify a successful update.
     *
     * @return void
     */
    protected function notifySuccessfulUpdate(): void
    {
        Session::flash('flash.message', __('notifications.successfully_updated'));
    }

    /**
     * Notify a general success message.
     *
     * @param string|null $message
     *
     * @return void
     */
    protected function notifyGeneralSuccess(?string $message = null): void
    {
        Session::flash('flash.message', $message ?? __('notifications.success'));
    }

    /**
     * Notify a general success message.
     *
     * @param string $message
     *
     * @return void
     */
    protected function notifyErrorMessage(string $message): void
    {
        Session::flash('flash.type', 'error');
        Session::flash('flash.message', $message);
    }

    /**
     * Abort 404 if request is not for a turbo frame.
     *
     * @return void
     */
    protected function abortNotTurbo(): void
    {
        // /** @var Request $request */
        // $request = request();

        // abort_unless($request->isForTurboFrame(), 404);
    }

    /**
     * Abort 404 if request is not for json.
     *
     * @return void
     */
    protected function abortNotJson(): void
    {
        /** @var Request $request */
        $request = request();

        abort_unless($request->wantsJson(), 404);
    }
}
