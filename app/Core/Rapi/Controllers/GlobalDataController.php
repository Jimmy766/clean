<?php

namespace App\Core\Rapi\Controllers;

use App\Core\Base\Traits\ApiResponser;
use App\Core\Rapi\Services\GlobalActiveService;
use App\Core\Rapi\Services\GlobalAvailableService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Swagger\Annotations as SWG;

/**
 * Class GlobalDataController
 * @package App\Http\Controllers
 */
class GlobalDataController extends Controller
{
    use ApiResponser;
    /**
     * @var GlobalAvailableService
     */
    private $globalAvailableService;
    /**
     * @var GlobalActiveService
     */
    private $globalActiveService;

    public function __construct(
        GlobalAvailableService $globalAvailableService,
        GlobalActiveService $globalActiveService
    ) {
        $this->middleware('client.credentials');
        $this->globalAvailableService = $globalAvailableService;
        $this->globalActiveService    = $globalActiveService;
    }

    /**
     * @SWG\Get(
     *   path="/global/services/available",
     *   summary="Get all available",
     *   tags={"Globals"},
     *   consumes={"application/json"},
     *
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *
     *
     *   @SWG\Response(response=200, ref="#/responses/200"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function globalAvailable(Request $request): JsonResponse
    {
        $data = $this->globalAvailableService->execute($request);

        return $this->successResponseWithMessage( $data );
    }

    /**
     * @SWG\Get(
     *   path="/global/products/active",
     *   summary="Get all active",
     *   tags={"Globals"},
     *   consumes={"application/json"},
     *
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *
     *
     *   @SWG\Response(response=200, ref="#/responses/200"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function globalActive(Request $request): JsonResponse
    {
        $data = $this->globalActiveService->execute($request);

        return $this->successResponseWithMessage($data, "", Response::HTTP_OK, true);
    }
}
