<?php

namespace App\Http\Middleware;

use App\Core\Base\Classes\ModelConst;
use App\Core\Blocks\Services\CheckBlockService;
use App\Core\Base\Services\CheckExceptionService;
use App\Core\Messages\Services\GenerateValidationMessageBlockService;
use Closure;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

/**
 * Class Localization
 * @package App\Http\Middleware
 */
class BlockMiddleware
{
    /**
     * @var CheckBlockService
     */
    private $checkBlockService;
    /**
     * @var \App\Core\Messages\Services\GenerateValidationMessageBlockService
     */
    private $generateValidationMessageBlockService;
    /**
     * @var CheckExceptionService
     */
    private $checkExceptionService;

    /**
     * Localization constructor.
     *
     * @param Application                           $app
     * @param CheckBlockService                     $checkBlockService
     * @param GenerateValidationMessageBlockService $generateValidationMessageBlockService
     * @param CheckExceptionService                 $checkExceptionService
     */
    public function __construct(
        Application $app, CheckBlockService $checkBlockService,
        GenerateValidationMessageBlockService $generateValidationMessageBlockService,
        CheckExceptionService $checkExceptionService
    ) {
        $this->app                                   = $app;
        $this->checkBlockService                     = $checkBlockService;
        $this->generateValidationMessageBlockService = $generateValidationMessageBlockService;
        $this->checkExceptionService = $checkExceptionService;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure                  $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $path = $request->path();
        $route = 'api/'.ModelConst::ROUTE_EXCEPTION;
        if($path=== $route){
            return $next($request);
        }

        $exceptions = $this->checkExceptionService->execute($request);
        $countExceptions = $exceptions->where('value','!=', null)->count();
        $hasExceptions = $countExceptions > 0 ? 'yes' : 'no';
        $validationsCollect = $this->checkBlockService->execute($request, $hasExceptions);
        $this->generateValidationMessageBlockService->execute($validationsCollect);

        // get the response after the request is done
        return $next($request);
    }
}





