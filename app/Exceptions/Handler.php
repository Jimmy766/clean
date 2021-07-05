<?php

namespace App\Exceptions;

use App\Core\Base\Classes\ModelConst;
use App\Core\Base\Services\LocationService;
use App\Core\Base\Services\GetInfoFromExceptionService;
use App\Core\Base\Services\SendLogConsoleService;
use App\Core\Base\Traits\ErrorNotificationTrait;
use Exception;
use App\Core\Base\Traits\ApiResponser;
use Illuminate\Database\QueryException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Response;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use GuzzleHttp\Client as ClientHttp;
use GuzzleHttp\RequestOptions;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponser;
    use ErrorNotificationTrait;

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        //commented for debugging
        // \Illuminate\Auth\AuthenticationException::class,
        //\Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param \Exception $exception
     * @return void
     */
    public function report(Throwable $exception)
    {
        if (app()->bound('sentry') && $this->shouldReport($exception)) {
            app('sentry')->captureException($exception);
        }
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception               $exception
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $exception)
    {

        $location = new LocationService();
        $location->setLanguageByHeaders($request);

        $infoEndpoint = GetInfoFromExceptionService::execute($request, $exception);

        $sendLogConsoleService = new SendLogConsoleService();
        $sendLogConsoleService->execute(
            $request,
            'errors',
            'errors',
            'error:' . $exception->getMessage(),
            $infoEndpoint
        );

        if ($exception instanceof ValidationException) {
            return $this->convertValidationExceptionToResponse($exception, $request);
        }

        if($exception->getCode() === Response::HTTP_UNPROCESSABLE_ENTITY){
            return $this->errorResponse(
                $exception->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        if ($exception instanceof ModelNotFoundException) {
            $model = strtolower(class_basename($exception->getModel()));
            return $this->errorResponse(__('There is no instance of') . " {$model} " . __('with the specified
            id'), \Symfony\Component\HttpFoundation\Response::HTTP_NOT_FOUND);
        }

        if ($exception instanceof AuthenticationException) {
            return $this->unauthenticated($request, $exception);
        }

        if ($exception instanceof AuthorizationException) {
            $this->sendErrorNotification(
                $infoEndpoint,
                ModelConst::PERMISSION_DENIED_EXCEPTION_ERROR,
                'slack'
            );
            return $this->errorResponse(__('You do not have permissions to execute this action'),
                \Symfony\Component\HttpFoundation\Response::HTTP_UNAUTHORIZED);
        }

        if ($exception instanceof NotFoundHttpException) {
            $data = [ 'url' => $request->fullUrl(), 'method' => $request->method() ];
            return $this->errorResponseWithMessage($data, __('The specified URL was not found'), \Symfony\Component\HttpFoundation\Response::HTTP_NOT_FOUND);
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            $this->sendErrorNotification(
                $infoEndpoint,
                ModelConst::INVALID_METHOD_EXCEPTION_ERROR
            );
            return $this->errorResponse(__('The specified method is invalid'), \Symfony\Component\HttpFoundation\Response::HTTP_METHOD_NOT_ALLOWED);
        }

        if ($exception instanceof HttpException) {
            $this->sendErrorNotification(
                $infoEndpoint,
                ModelConst::HTTP_EXCEPTION_ERROR
            );
            return $this->errorResponse($exception->getMessage(), $exception->getStatusCode());
        }

        if ($exception instanceof QueryException) {
            $this->sendErrorNotification(
                $infoEndpoint,
                ModelConst::QUERY_EXCEPTION_ERROR
            );
            $code = $exception->errorInfo[ 1 ];
            if ($code == 1451) {
                return $this->errorResponse('You can not delete the resource because the resource is related with someone else.', \Symfony\Component\HttpFoundation\Response::HTTP_CONFLICT);
            }
            return $this->errorResponse('Query error. ' . $exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if ($exception instanceof TokenMismatchException) {
            return redirect()->back()->withInput($request->input());
        }


        if(env('APP_ENV', null) !== 'dev' && env('APP_ENV', null) !== 'local'){
            $this->sendErrorNotification(
                $infoEndpoint,
                ModelConst::TOTAL_EXCEPTION_ERROR
            );
        }

            return $this->errorCatchResponse($exception, __('Unexpected failure. Try later'));
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Auth\AuthenticationException $exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($this->isFrontend($request)) {
            return redirect()->guest('login');
        }
        return $this->errorResponse(__('No authenticated.'), Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Create a response object from the given validation exception.
     *
     * @param \Illuminate\Validation\ValidationException $e
     * @param \Illuminate\Http\Request                   $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        $errors = $e->validator->errors()->getMessages();
        if ($this->isFrontend($request)) {
            return $request->ajax()
                ? response()->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY) : redirect()
                    ->back()
                    ->withInput($request->input())
                    ->withErrors($errors);
        }
        return $this->errorResponse($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function isFrontend($request)
    {
        return $request->acceptsHtml() && collect($request->route()->middleware())->contains('web');
    }


}
