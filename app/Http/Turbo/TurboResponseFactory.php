<?php

namespace App\Http\Turbo;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;

class TurboResponseFactory
{
    /** @var string */
    public const TURBO_STREAM_FORMAT = 'text/vnd.turbo-stream.html';

    /**
     * @param View $view
     * @param int  $status
     *
     * @return Response|ResponseFactory
     */
    public static function makeStream(View $view, int $status = 200): Response|ResponseFactory
    {
        $alertViewHtml = Session::has('flash.message')
            ? view('partials.ui.streamed-alert', ['type' => Session::pull('flash.type', 'success'), 'message' => Session::pull('flash.message')])->render()
            : '';

        return response($view->render() . $alertViewHtml, $status, ['Content-Type' => static::TURBO_STREAM_FORMAT]);
    }
}
