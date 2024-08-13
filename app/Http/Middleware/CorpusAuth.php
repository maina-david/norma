<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

/**
 * Very basic auth for requests coming from Corpus.
 */
class CorpusAuth
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param Closure                  $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->header('Authorization')) {
            throw new AuthenticationException();
        }

        if ($request->header('Authorization') !== config('api.corpus_webhook_secret')) {
            throw new AuthenticationException();
        }

        return $next($request);
    }
}
