<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Class CheckExternalAccess
 * @package App\Http\Middleware
 */
class CheckExternalAccess
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $environment = env('HOST_KEY_EXTERNAL_ACCESS', null);

        if ($environment === null) {
            abort(Response::HTTP_UNAUTHORIZED, 'not exist environment external service');
        }

        $validSecrets = explode('|', $environment);
        $validSecrets = collect($validSecrets);

        $validSecrets = $validSecrets->map($this->mapGetEnvironmentKeyAndAccessTransform());

        $keyAccess  = $request->header('key-access');
        $hostAccess = $request->header('host');

        $countAccess = $validSecrets->where('host_access', $hostAccess)
            ->where('key_access', $keyAccess)
            ->count();

        if ($keyAccess === null || $hostAccess === null) {
            abort(Response::HTTP_UNAUTHORIZED, 'unauthenticated external service');
        }

        if ($countAccess === 0) {
            abort(Response::HTTP_UNAUTHORIZED, 'unauthenticated external service 2');
        }

        return $next($request);
    }

    private function mapGetEnvironmentKeyAndAccessTransform(): callable
    {
        return function ($item, $key) {
            $hostAndKey = explode(',', $item);
            $newItem    = (object) [];

            $newItem->host_access = $hostAndKey[ 0 ];
            $newItem->key_access  = $hostAndKey[ 1 ];

            return $newItem;
        };
    }

}
