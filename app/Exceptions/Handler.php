<?php

namespace App\Exceptions;

use App\Actions\System\ApiLog\LogApiCall;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use League\OAuth2\Server\Exception\OAuthServerException;
use Sentry\Laravel\Integration;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/** @codeCoverageIgnore */
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            if (config('services.sentry.enabled')) {
                Integration::captureUnhandledException($e);
            }
        });
    }

    /**
     * Convert a validation exception into a response.
     *
     * @param \Illuminate\Http\Request                   $request
     * @param \Illuminate\Validation\ValidationException $exception
     *
     * @return \Illuminate\Http\Response
     */
    protected function invalid($request, ValidationException $exception)
    {
        $response = parent::invalid($request, $exception);

        /** @var \Illuminate\Http\Response */
        return $request->isHotwire()
            ? $response->setStatusCode(422)
            : $response;
    }

    /**
     * @param Throwable $e
     *
     * @return void
     */
    public function report(Throwable $e)
    {
        // don't report authentication exceptions
        if ($e instanceof OAuthServerException && $e->getHttpStatusCode() == 401) {
            return;
        }

        parent::report($e);
    }

    /**
     * Overrides parent so we always return JSON, regardless of whether Accept:application/json header is set.
     *
     * @param \Illuminate\Http\Request $request
     * @param Throwable                $e
     *
     * @return bool
     */
    protected function shouldReturnJson($request, Throwable $e)
    {
        if ($request->routeIs('api.')) {
            return true;
        }

        return parent::shouldReturnJson($request, $e);
    }

    /**
     * Prepare a response for the given exception.
     *
     * {@inheritdoc}
     */
    protected function prepareResponse($request, Throwable $e)
    {
        if ($request->routeIs('api.')) {
            if ($e instanceof OAuthServerException) {
                $statusCode = $e->getHttpStatusCode();
                $headers = $e->getHttpHeaders();
                $e = new HttpException($statusCode, $e->getMessage(), $e, $headers, $e->getCode());
            }
        }

        return parent::prepareResponse($request, $e);
    }

    /**
     * Override parent so we can add api call check.
     *
     * {@inheritdoc}
     */
    protected function renderExceptionResponse($request, Throwable $e)
    {
        $response = parent::renderExceptionResponse($request, $e);

        // need to add this as the middleware is only attached to API routes, but if it's a 404 then it's possibly
        // because it has not found a route
        if ($e instanceof HttpExceptionInterface && $e->getStatusCode() === 404 && str_contains($request->getUri(), 'api.')) {
            LogApiCall::run($request, $response);
        }

        return $response;
    }
}
