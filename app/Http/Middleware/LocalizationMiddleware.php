<?php

namespace App\Http\Middleware;

use App\Core\Base\Services\LocationService;
use Closure;

use Illuminate\Foundation\Application;

/**
 * Class LocalizationMiddleware
 * @package App\Http\Middleware
 */
class LocalizationMiddleware
{

    /**
     * @var LocationService
     */
    private $locationService;

    /**
     * Localization constructor.
     *
     * @param \Illuminate\Foundation\Application $app
     * @param LocationService                    $locationService
     */
    public function __construct(Application $app, LocationService $locationService)
    {

        $this->app             = $app;
        $this->locationService = $locationService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $locale = $this->locationService->setLanguageByHeaders($request, $this->app);

        // get the response after the request is done
        $response = $next($request);

        // set Content Languages header in the response
        $response->headers->set('Content-Language', $locale);

        // return the response
        return $response;
    }
}





