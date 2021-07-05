<?php


namespace App\Core\Lotteries\Controllers;


use App\Core\Telem\Requests\TelemSyndicateWheelsRequest;
use App\Core\Lotteries\Models\Lottery;
use App\Core\Telem\Models\TelemUserSystem;
use App\Core\Telem\Transforms\TelemLotteryWheelTransformer;
use App\Http\Controllers\ApiController;
use DB;


class LotteryWheelController extends ApiController
{
    public function __construct() {
        parent::__construct();
        $this->middleware('client.credentials');
    }


    /**
     * @SWG\Get(
     *   path="/lottery_wheels",
     *   summary="list lottery_wheels telem syndicate",
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
     *     name="type",
     *     in="query",
     *     description="type (show all=full)",
     *     required=false,
     *     type="string"
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
    public function index(TelemSyndicateWheelsRequest $request){

        $sys_id = $request->client_sys_id;
        $agent_id = $request->agent_id;
        $is_full = $request->has("type") && $request->get("type") == "full" ? true : false;

        $agent = TelemUserSystem::findOrFail($agent_id);

        $has_lotteries = $is_full ? $agent->hasLotteryWheelsFullAvailable() : $agent->hasLotteryWheelsAvailable();

        if($has_lotteries){
            $lotteries_allowed =  $is_full ?
                $agent->lotteryWheelsFull() : $agent->lotteryWheels();

            $draws_to_show = DB::connection("mysql_external")
                ->select("SELECT lot_id, draw_id, draw_date, draw_default_to_show FROM draws
WHERE draw_special_status IN (1,2) AND draw_is_on_sale
ORDER BY lot_id ASC, draw_default_to_show DESC, draw_date ASC");
            $draws = [];
            foreach($draws_to_show as $draw){
                $draws[$draw->lot_id][] = [
                    "lot_id" => $draw->lot_id,
                    "draw_id" => $draw->draw_id,
                    "draw_date" => $draw->draw_date,
                    "draw_default_to_show" => $draw->draw_default_to_show
                ];
            }

            $lotteries = Lottery::select("lotteries.curr_code", "lotteries.lot_id", "lot_name_en", "lot_balls", "lot_extra", "lot_pick_balls",
                "lot_max_draws_playing", "lot_pick_extra", "lot_auto_pick_reintegro", "lot_pick_reintegro", "lot_extra_startNum",
                "lot_maxNum", "lot_extra_maxNum", "slip_min_lines")
                ->join("draws", "draws.lot_id", "=", "lotteries.lot_id")
                ->where("lotteries.lot_active", "=", 1);


            if($lotteries_allowed != ""){
                $lotteries->whereIn("lotteries.lot_id", explode(",", $lotteries_allowed));
            }

            $lotteries = $lotteries->orderBy("lot_name_en")->distinct("lotteries.lot_id")->get();

            foreach($lotteries as $lottery){
                $lottery->draws_to_show = isset($draws[$lottery->lot_id]) ? $draws[$lottery->lot_id] : [];
            }

            if($lotteries->isNotEmpty()){
                $lotteries->first()->transformer = TelemLotteryWheelTransformer::class;
            }

            return $this->showAllNoPaginated($lotteries);
        }

        return $this->successResponse(array('data' => []), 200);

    }
}
