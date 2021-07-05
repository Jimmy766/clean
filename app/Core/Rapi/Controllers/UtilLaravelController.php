<?php

namespace App\Core\Rapi\Controllers;

use App\Core\Base\Classes\ModelConst;
use App\Core\Base\Traits\ApiResponser;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Swagger\Annotations as SWG;

class UtilLaravelController extends Controller
{
    use ApiResponser;

    public function __construct()
    {
        $this->middleware('client.credentials')->except( ['tokenToSsr']);
    }

    /**
     * @SWG\Get(
     *   path="/util-laravel/list-routes",
     *   summary="Show routes list API ",
     *   tags={"Utils Laravel"},
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(
     *     response="403",
     *     description="Forbidden Access",
     *     @SWG\Schema(
     *       @SWG\Property(property="error", type="string", description="Message error",
     *                                       example="This data is not allowed for you"),
     *       @SWG\Property(property="code", type="integer", description="Response code",
     *                                      example="403"),
     *     ),
     *   ),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function listRoutes()
    {
        $routeCollection = Route::getRoutes();

        $newCollection      = collect([]);
        $newCleanCollection = collect([]);
        foreach ($routeCollection as $route) {
            $uri      = $route->uri;
            $explode  = explode("{", $uri);
            $uriClean = $explode[ 0 ];

            $lastCharacter = substr($uriClean, -1);
            if ($lastCharacter === '/') {
                $uriClean = substr(trim($uriClean), 0, -1);
            }

            if ($uri !== 'api/util-laravel/list-routes') {
                $newCollection->push($uri);
            }
            if ( !empty($uriClean) && $uri !== 'api/util-laravel/list-routes') {
                $newCleanCollection->push($uriClean);
            }
        }
        $newCleanCollection          = $newCleanCollection->unique();
        $response [ 'routes' ]       = $newCollection;
        $response [ 'routes_clean' ] = $newCleanCollection->values();
        return $this->successResponseWithMessage($response);
    }

    /**
     * @SWG\Get(
     *   path="/util-laravel/token-ssr",
     *   summary="get token to process ssr",
     *   tags={"Utils Laravel"},
     *   security={
     *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Parameter(
     *     name="force_generate",
     *     in="query",
     *     description="",
     *     type="string",
     *     default=""
     *   ),
     *   @SWG\Response(response=200, ref="#/responses/200"),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(
     *     response="403",
     *     description="Forbidden Access",
     *     @SWG\Schema(
     *       @SWG\Property(property="error", type="string", description="Message error",
     *                                       example="This data is not allowed for you"),
     *       @SWG\Property(property="code", type="integer", description="Response code",
     *                                      example="403"),
     *     ),
     *   ),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function tokenToSsr( Request $request ): JsonResponse
    {

        $parameterSet = $request->header( 'tkssrga' );
        $typeClient = $request->header( 'tk-client' );
        if ( $parameterSet === null ) {
            return $this->errorResponseWithMessage();
        }

        $forceGenerate = $request->force_generate;

        $key   = ModelConst::KEY_CACHE_TOKEN_SSR;
        if($typeClient!== null){
            $key .= "-" . $typeClient;
        }
        $token = Cache::get( $key );

        if ( $token !== null && empty($forceGenerate) ) {
            $data[ 'token' ] = $token;

            return $this->successResponseWithMessage( $data );
        }

        $ttl    = ModelConst::CACHE_TIME_DAY;
        $date   = Carbon::now()->format( 'Y-m-d H:i:s' );
        $string = "ssr-password-{$date}";
        $token  = Hash::make( $string );
        Cache::put( $key, $token, $ttl );

        $data[ 'token' ] = $token;
        $data[ 'time' ]  = $ttl;

        return $this->successResponseWithMessage( $data );
    }
}
