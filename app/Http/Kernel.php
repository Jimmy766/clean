<?php

namespace App\Http;

use App\Http\Middleware\BlockMiddleware;
use App\Http\Middleware\LocalizationMiddleware;
use App\Http\Middleware\ThrottleRequestsMiddleware;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\TrustProxies::class,
        \App\Http\Middleware\ApiLogger::class,
        \App\Http\Middleware\PerformanceCheck::class,
        \Fruitcake\Cors\HandleCors::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            //            'throttle:100000,1',
            //            'throttle:30,1',
            //check limit \App\Http\Middleware\ThrottleRequestsMiddleware::handle(maxAttempts) line 42
//            'throttle' => ThrottleRequestsMiddleware::class,
            'bindings',
            BlockMiddleware::class,
            LocalizationMiddleware::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'check.ip' => \App\Http\Middleware\CheckIp::class,
        'check.external_access' => \App\Http\Middleware\CheckExternalAccess::class,
        //'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'client.credentials' => \App\Http\Middleware\CheckClientCredentials::class,
        //'client.credentials' => \Laravel\Passport\Http\Middleware\CheckClientCredentials::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'scope' => \Laravel\Passport\Http\Middleware\CheckForAnyScope::class,
        'scopes' => \Laravel\Passport\Http\Middleware\CheckScopes::class,
        'transform.input' => \App\Http\Middleware\TransformInput::class,
        'performance.check' => \App\Http\Middleware\PerformanceCheck::class,
    ];
}
