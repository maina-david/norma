<?php

namespace App\Http;

use App\Http\Middleware\ActiveOrganisation;
use App\Http\Middleware\ActiveUser;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\CorpusAuth;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\LogApiCalls;
use App\Http\Middleware\ModuleEnabled;
use App\Http\Middleware\PreventExternalClients;
use App\Http\Middleware\PreventNonOrganisationalClients;
use App\Http\Middleware\PreventRequestsDuringMaintenance;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\SetCorrectApp;
use App\Http\Middleware\SetCorrectSessionDomain;
use App\Http\Middleware\SetWhiteLabel;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\VerifyCsrfToken;
use HotwiredLaravel\TurboLaravel\Http\Middleware\TurboMiddleware;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Laravel\Passport\Http\Middleware\CheckClientCredentials;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, string>
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        TrustProxies::class,
        HandleCors::class,
        PreventRequestsDuringMaintenance::class,
        ValidatePostSize::class,
        TrimStrings::class,
        ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, string>>
     */
    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            SetCorrectSessionDomain::class,
            StartSession::class,
            SetWhiteLabel::class,
            AuthenticateSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            TurboMiddleware::class,
            HandleInertiaRequests::class,
        ],

        'api' => [
            'throttle:api',
            SubstituteBindings::class,
            LogApiCalls::class,
        ],

        'api:stateful' => [
            'throttle:api',
            SubstituteBindings::class,
            EnsureFrontendRequestsAreStateful::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array<string, string>
     */
    protected $middlewareAliases = [
        'active.org' => ActiveOrganisation::class,
        'active.user' => ActiveUser::class,
        'auth.basic' => AuthenticateWithBasicAuth::class,
        'client' => CheckClientCredentials::class,
        'client.internal' => PreventExternalClients::class,
        'client.org' => PreventNonOrganisationalClients::class,
        'auth' => Authenticate::class,
        'cache.headers' => SetCacheHeaders::class,
        'can' => Authorize::class,
        'corpusauth' => CorpusAuth::class,
        'guest' => RedirectIfAuthenticated::class,
        'module' => ModuleEnabled::class,
        'password.confirm' => RequirePassword::class,
        'set-app' => SetCorrectApp::class,
        'signed' => ValidateSignature::class,
        'throttle' => ThrottleRequests::class,
        'verified' => EnsureEmailIsVerified::class,
    ];
}
