<?php

namespace App\Core\Syndicates\Controllers;

use App\Core\Telem\Requests\TelemSyndicateWheelsRequest;
use App\Core\Telem\Services\TelemService;
use App\Core\Syndicates\Models\Syndicate;
use App\Core\Telem\Models\TelemUserSystem;
use App\Core\Base\Traits\Pixels;
use App\Core\Telem\Transforms\TelemSyndicateWheelTransformer;
use App\Http\Controllers\ApiController;
use DB;

class SyndicateWheelController extends ApiController
{

    use Pixels;

    public function __construct() {
        parent::__construct();
        $this->middleware('auth:api')->except('show', 'index', 'lotteries', 'prices');
        $this->middleware('client.credentials')->only('index', 'show', 'lotteries', 'prices');
    }


    /**
     * @SWG\Get(
     *   path="/syndicate_wheels",
     *   summary="list syndicate_wheels telem syndicate",
     *   tags={"wheels"},
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Parameter(
     *     name="agent_id",
     *     in="query",
     *     description="agent_id",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="client_sys_id",
     *     in="query",
     *     description="client_sys_id",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array",
     *                                        @SWG\Items(ref="#/definitions/Asset")),
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
    public function index(TelemSyndicateWheelsRequest $request) {
        $sys_id = $request->client_sys_id;
        $agent_id = $request->agent_id;

        $agent = TelemUserSystem::findOrFail($agent_id);

        if($agent->hasSyndicateWheelsAvailable()){
            $syndicates_allowed = $agent->syndicateWheels();
            $syndicates = Syndicate::select("printable_name", "id")
                ->where("active_admin_telem", "=", 1)
                ->where("sys_id", "=", $sys_id)
                ->where("has_wheel", "=", 1);

            if($syndicates_allowed != ""){
                $syndicates->whereIn("id", explode(",", $syndicates_allowed));
            }

            $syndicates = $syndicates->get();

            if($syndicates->isNotEmpty()){
                $syndicates->first()->transformer = TelemSyndicateWheelTransformer::class;
            }

            return $this->showAllNoPaginated($syndicates);
        }

        return $this->successResponse(array('data' => []), 200);
    }


}
