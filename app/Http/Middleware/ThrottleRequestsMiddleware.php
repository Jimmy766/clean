<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ThrottleRequestsMiddleware
 * @package App\Http\Middleware
 */
class ThrottleRequestsMiddleware
{
    /**
     * The rate limiter instance.
     *
     * @var RateLimiter
     */
    protected $limiter;

    /**
     * Create a new request throttler.
     *
     * @param RateLimiter $limiter
     */
    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param int     $maxAttempts
     * @param int     $decaySeconds
     * @return mixed
     */
    public function handle($request, Closure $next, $maxAttempts = 100000, $decaySeconds = 180)
    {
        $path = $request->path();
        $route = 'api/oauth/token';
        if($path=== $route){
            return $next($request);
        }
        $key = $this->resolveRequestSignature($request);

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            return $this->buildResponse($key, $maxAttempts);
        }

        $this->limiter->hit($key, $decaySeconds);

        $response = $next($request);

        return $this->addHeaders(
            $response, $maxAttempts, $this->calculateRemainingAttempts($key, $maxAttempts)
        );
    }

    /**
     * Resolve request signature.
     *
     * @param Request $request
     * @return string
     */
    protected function resolveRequestSignature($request)
    {
        return $request->fingerprint();
    }

    /**
     * Create a 'too many attempts' response.
     *
     * @param string $key
     * @param int    $maxAttempts
     * @return \Illuminate\Http\Response
     */
    protected function buildResponse($key, $maxAttempts)
    {
        $message = json_encode(
            [
                'error'  => [
                    //check limit \App\Http\Middleware\ThrottleRequestsMiddleware::handle(maxAttempts) line 42
                    'message' => 'Too many attempts request' //may comes from lang file
                ],
                'status' => 429 //your custom code
            ]
        );

        $response = new Response($message, 429);

        $retryAfter = $this->limiter->availableIn($key);

        return $this->addHeaders(
            $response, $maxAttempts, $this->calculateRemainingAttempts($key, $maxAttempts, $retryAfter),
            $retryAfter
        );
    }

    /**
     * Add the limit header information to the given response.
     *
     * @param Response $response
     * @param int      $maxAttempts
     * @param int      $remainingAttempts
     * @param int|null $retryAfter
     * @return \Illuminate\Http\Response|Response
     */
    protected function addHeaders(Response $response, $maxAttempts, $remainingAttempts, $retryAfter = null)
    {
        $headers = [
            'X-RateLimit-Limit'     => $maxAttempts,
            'X-RateLimit-Remaining' => $remainingAttempts,
        ];

        if ( !is_null($retryAfter)) {
            $headers[ 'Retry-After' ]  = $retryAfter;
            $headers[ 'Content-Type' ] = 'application/json';
        }

        $response->headers->add($headers);

        return $response;
    }

    /**
     * Calculate the number of remaining attempts.
     *
     * @param string   $key
     * @param int      $maxAttempts
     * @param int|null $retryAfter
     * @return int
     */
    protected function calculateRemainingAttempts($key, $maxAttempts, $retryAfter = null)
    {
        if ( !is_null($retryAfter)) {
            return 0;
        }

        return $this->limiter->retriesLeft($key, $maxAttempts);
    }
}
