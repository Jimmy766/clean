<?php

namespace App\Core\Rapi\Controllers;

use App\Core\Rapi\Resources\RoutingFriendlyResource;
use App\Core\Rapi\Models\RoutingFriendly;
use App\Core\Base\Traits\ApiResponser;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Swagger\Annotations as SWG;

class RoutingFriendlyController extends Controller
{
    use ApiResponser;

    public function __construct()
    {
        $this->middleware('client.credentials');
    }

    /**
     * @SWG\Get(
     *   path="/routing-friendly",
     *   summary="Show routing friendly list ",
     *   tags={"Routing-Friendly"},
     *   @SWG\Parameter(
     *     name="type_product",
     *     in="query",
     *     description="type product",
     *     type="integer",
     *   ),
     *   @SWG\Parameter(
     *     name="partial_path",
     *     in="query",
     *     description="path to search",
     *     type="string",
     *   ),
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array",
     *                                        @SWG\Items(ref="#/definitions/RoutingFriendly")),
     *     ),
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
    public function index(Request $request)
    {
        $idSys                = $request[ 'client_sys_id' ];
        $routingFriendlyQuery = RoutingFriendly::query();
        $routingFriendlyQuery = $routingFriendlyQuery->where('sys_id', $idSys);
        if ($request->type_product) {
            $routingFriendlyQuery = $routingFriendlyQuery->where('element_type', $request->type_product);
        }
        if ($request->partial_path) {
            $routingFriendlyQuery = $routingFriendlyQuery->where('element_name', $request->partial_path);
        }
        $routingFriendly = $routingFriendlyQuery->getFromCache();

        $data[ 'routing_friendly' ] = RoutingFriendlyResource::collection($routingFriendly);

        return $this->successResponseWithMessage($data);
    }
}
