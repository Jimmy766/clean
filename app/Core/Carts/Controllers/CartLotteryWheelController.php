<?php


namespace App\Core\Carts\Controllers;


use App\Core\Carts\Models\CartSubscription;
use App\Core\Carts\Models\CartSubscriptionPick;
use App\Core\Carts\Requests\CartLotteryWheelCreateRequest;
use App\Core\Carts\Requests\CartLotteryWheelDeleteRequest;
use App\Core\Carts\Requests\CartLotteryWheelEditRequest;
use App\Core\Rapi\Services\Log;
use App\Core\Rapi\Services\Util;
use App\Core\Lotteries\Models\Lottery;
use App\Core\Rapi\Models\Price;
use App\Core\Lotteries\Services\LotteryService;
use App\Core\Telem\Services\TelemService;
use App\Core\Base\Traits\CartUtils;
use App\Core\Base\Traits\PicksValidation;
use App\Http\Controllers\ApiController;
use DB;
use Swagger\Annotations as SWG;

class CartLotteryWheelController extends ApiController
{

    use CartUtils;
    use PicksValidation;

    public function __construct() {
        parent::__construct();
        $this->middleware('client.credentials')->except('index', 'details');
        $this->middleware('auth:api')->only('index', 'details');
    }

    /**
     * @SWG\Post(
     *   path="/cart_lottery_wheels",
     *   summary="Create Cart Lottery wheels",
     *   tags={"Cart Wheels"},
     *   consumes={"multipart/form-data"},
     *   @SWG\Parameter(
     *     name="crt_id",
     *     in="formData",
     *     description="Cart crt_id",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="prc_id",
     *     in="formData",
     *     description="Cart prc_id",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="lot_id",
     *     in="formData",
     *     description="Cart lot_id",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="cts_ticket_byDraw",
     *     in="formData",
     *     description="cts_ticket_byDraw",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="cts_pck_type",
     *     in="formData",
     *     description="cts_pck_type 1 to 3",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="pick_balls",
     *     in="formData",
     *     description="pick_balls if cts_pck_type is 3",
     *     type="integer"
     *   ),
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=201,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(property="data", allOf={ @SWG\Schema(ref="#/definitions/Cart"), }),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function store( CartLotteryWheelCreateRequest $request)
    {
        try{


            $validation = $this->validateCart($request->crt_id);
            if ($validation)
                return $validation;
            $lock = $this->check_for_cart_lock($request->crt_id);
            if ($lock)
                return $lock;

            $crt_id = $request->crt_id;
            $sys_id = $request->client_sys_id;
            $curr_code = $request->country_currency;
            $country_id = $request->user_country;
            $prc_id = $request->prc_id;
            $ticket_byDraw = $request->ticket_byDraw;
            $pick_type = $request->cts_pck_type;

            $wheel_info = new \stdClass();

            // obtengo info de tickets y extension del registro del precio
            $sql = "SELECT p.prc_draws, p.prc_time,	p.prc_time_type, p.prc_days_by_tickets, w.wheel_id, w.wheel_balls, w.wheel_lines, w.wheel_type
					FROM prices p INNER JOIN prices_line pl ON p.prc_id = pl.prc_id INNER JOIN wheels w ON p.wheel_id = w.wheel_id
					WHERE p.sys_id = ". $sys_id ." AND p.prc_draws <> 0 AND prc_time <> 0
					AND pl.curr_code = '". $curr_code ."' AND p.prc_id = ".$prc_id."
					AND (prcln_country_list_enabled = 0 OR prcln_country_list_enabled LIKE '%".$country_id."%')
					AND (prcln_country_list_disabled NOT LIKE '%".$country_id."%')";

            $infotmp = DB::connection("mysql_external")->select($sql);

            if (!isset($infotmp[0]) || !isset($infotmp[0]->prc_draws))
            {
                return $this->errorResponse(trans("lottery_price_invalid"), 422);
            }

            $infotmp = $infotmp[0];
            $price = Price::findOrFail($prc_id);
            $new_tickets	= $infotmp->wheel_lines * $infotmp->prc_draws * $ticket_byDraw;
            $new_extension	= $infotmp->prc_time;
            $wheel_info->wheel_id		= $infotmp->wheel_id;
            $wheel_info->wheel_balls	= $infotmp->wheel_balls;
            $wheel_info->wheel_type	= $infotmp->wheel_type;
            //TODAS LAS LOTTOS juegan 1 draw por ticket y en todos los draws
            $new_day_to_play = 7;
            $new_draws_by_ticket = 1;

            DB::beginTransaction();

            $cart_subscription = new CartSubscription();

            $cart_subscription->lot_id = $request->lot_id;
            $cart_subscription->crt_id = $crt_id;
            $cart_subscription->cts_subExtension = $new_extension;
            $cart_subscription->cts_tickets = $new_tickets;
            $cart_subscription->cts_price = $tickets_price = $price->price_line['price'];
            $cart_subscription->cts_ticket_extra = 0; //no se usa;
            $cart_subscription->cts_pck_type = $pick_type;
            $cart_subscription->cts_ticket_byDraw = $request->cts_ticket_byDraw;
            $cart_subscription->cts_ticket_nextDraw = 1;//No se usa
            $cart_subscription->cts_day_to_play = $new_day_to_play;
            $cart_subscription->cts_draws_by_ticket = $new_draws_by_ticket;
            $cart_subscription->cts_prc_id = $prc_id;
            $cart_subscription->cts_next_draw_id = 0; //ya no se usa
            $cart_subscription->wheel_id = $wheel_info->wheel_id;
            $cart_subscription->cts_printable_name = '';
            $cart_subscription->cts_winning_behaviour = 1;
            $cart_subscription->cts_renew = 1;
            $cart_subscription->lot_id_big = 0;

            $cart_subscription->save();

            $lottery = Lottery::select("lot_id", "lot_maxNum", "lot_pick_balls",
                "lot_pick_extra", "lot_extra_startNum", "slip_min_lines", "lot_name_en",
                "lot_auto_pick_extra", "lot_pick_reintegro", "lot_extra_startNum",
                "lot_auto_pick_reintegro", "lot_extra_maxNum")->findOrFail($request->lot_id);

            $pcks = LotteryService::wheelPicks($pick_type, $request->pick_balls, $request->pick_extra_balls, $lottery,
                $wheel_info, $request->cts_ticket_byDraw);

            if(is_string($pcks)) {
                DB::rollback();
                return $this->errorResponse($pcks, 422);
            }

            foreach($pcks as $pck) {
                $str_wheel_picked_balls = implode(",", $pck["balls"]);
                $str_wheel_picked_extras = implode(",", $pck["extras"]);

                $cart_subscriptions_picks = new CartSubscriptionPick();
                $cart_subscriptions_picks->cts_id = $cart_subscription->cts_id;

                $cart_subscriptions_picks->cts_wheel_picked_balls = $str_wheel_picked_balls;

                $cart_subscriptions_picks->cts_wheel_picked_extras = $str_wheel_picked_extras;

                $cart_subscriptions_picks->save();
            }


            $cart = $cart_subscription->cart;
            $cart->crt_total += $cart_subscription->cts_price;
            $this->cartAmounts($cart);
            $request->merge(['pixel' => $cart->cart_step1()]);

            DB::commit();
            return $this->showOne($cart, 201);

        }catch (\Exception $ex){
            DB::rollback();
            Log::record_log("access", "STORE_LOTTERY_WHEELS_ERROR" . $ex->getMessage() . " " . $ex->getFile() . ": " . $ex->getLine());
            return $this->errorResponse("There has been an error", 422);
        }
    }

    /*
     *  'crt_id' => 'required|integer|exists:mysql_external.carts',
            *'prc_id' => 'required|integer|exists:mysql_external.prices,prc_id',
            *'cts_ticket_byDraw' => 'required|integer|min:1|max:10',
            *'cts_pck_type' => 'required|integer|in:1,3',
            *'pick_balls' => 'required_if:cts_pck_type,3|array'
     */

    /**
     * @SWG\Put(
     *   path="/cart_lottery_wheels/{cart_lottery_wheel}",
     *   summary="Update Cart Lottery details ",
     *   tags={"Cart Wheels"},
     *   @SWG\Parameter(
     *     name="cart_lottery_wheel",
     *     in="path",
     *     description="Cart Lottery Wheel Id.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="crt_id",
     *     in="formData",
     *     description="Cart crt_id",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="prc_id",
     *     in="formData",
     *     description="Cart prc_id",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="lot_id",
     *     in="formData",
     *     description="Cart lot_id",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="cts_ticket_byDraw",
     *     in="formData",
     *     description="cts_ticket_byDraw",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="cts_pck_type",
     *     in="formData",
     *     description="cts_pck_type 1 to 3",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="pick_balls",
     *     in="formData",
     *     description="pick_balls if cts_pck_type is 3",
     *     type="integer"
     *   ),
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=201,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(property="data", allOf={ @SWG\Schema(ref="#/definitions/Cart"), }),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function update( $id, CartLotteryWheelEditRequest $request)
    {
        $validation = $this->validateCart($request->crt_id);

        if ($validation)
            return $validation;
        $lock = $this->check_for_cart_lock($request->crt_id);
        if ($lock)
            return $lock;

        $sys_id = $request->client_sys_id;
        $curr_code = $request->country_currency;
        $country_id = $request->user_country;
        $prc_id = $request->prc_id;
        $ticket_byDraw = $request->ticket_byDraw;

        $wheel_info = new \stdClass();

        // obtengo info de tickets y extension del registro del precio
        $sql = "SELECT p.prc_draws, p.prc_time,	p.prc_time_type, p.prc_days_by_tickets, w.wheel_id, w.wheel_balls, w.wheel_lines, w.wheel_type
					FROM prices p INNER JOIN prices_line pl ON p.prc_id = pl.prc_id INNER JOIN wheels w ON p.wheel_id = w.wheel_id
					WHERE p.sys_id = ". $sys_id ." AND p.prc_draws <> 0 AND prc_time <> 0
					AND pl.curr_code = '". $curr_code ."' AND p.prc_id = ".$prc_id."
					AND (prcln_country_list_enabled = 0 OR prcln_country_list_enabled LIKE '%".$country_id."%')
					AND (prcln_country_list_disabled NOT LIKE '%".$country_id."%')";

        $infotmp = DB::connection("mysql_external")->select($sql);


        if (!isset($infotmp[0]) || !isset($infotmp[0]->prc_draws))
        {
            return $this->errorResponse(trans("lottery_price_invalid"), 422);
        }

        $infotmp = $infotmp[0];
        $price = Price::findOrFail($prc_id);
        $new_tickets	= $infotmp->wheel_lines * $infotmp->prc_draws * $ticket_byDraw;
        $new_extension	= $infotmp->prc_time;
        $wheel_info->wheel_id		= $infotmp->wheel_id;
        $wheel_info->wheel_balls	= $infotmp->wheel_balls;
        $wheel_info->wheel_type	    = $infotmp->wheel_type;
        //TODAS LAS LOTTOS juegan 1 draw por ticket y en todos los draws
        $new_day_to_play = 7;
        $new_draws_by_ticket = 1;

        $cart_subscription = CartSubscription::where("cts_id", "=", $id)
            ->where("crt_id", "=", $request->crt_id)
            ->first();

        if(!$cart_subscription){
            return $this->errorResponse(trans("cart_invalid"), 422);
        }

        $cart_subscription->cts_subExtension = $new_extension;
        $cart_subscription->cts_tickets = $new_tickets;
        $cart_subscription->cts_price = $tickets_price = $price->price_line['price'];
        $cart_subscription->cts_ticket_byDraw = $request->cts_ticket_byDraw;

        if($request->has("cts_pck_type"))
            $cart_subscription->cts_pck_type = $request->cts_pck_type == 3 ? 3 : 1;

        $cart_subscription->cts_day_to_play = $new_day_to_play;
        $cart_subscription->cts_draws_by_ticket = $new_draws_by_ticket;
        $cart_subscription->cts_prc_id = $prc_id;
        $cart_subscription->wheel_id = $wheel_info->wheel_id;
        $cart_subscription->save();

        $lottery = Lottery::select("lot_id", "lot_maxNum", "lot_pick_balls",
            "lot_pick_extra", "lot_extra_startNum", "slip_min_lines", "lot_name_en",
            "lot_auto_pick_extra", "lot_pick_reintegro", "lot_extra_startNum",
            "lot_auto_pick_reintegro", "lot_extra_maxNum")->findOrFail($request->lot_id);

        if($request->has("cts_pck_type")) {

            $cart_subscription->cart_subscription_picks->each(function (CartSubscriptionPick $item) {
                $item->delete();
            });

            $pcks = LotteryService::wheelPicks($request->cts_pck_type, $request->pick_balls,
                $request->pick_extra_balls, $lottery,
                $wheel_info, $request->cts_ticket_byDraw);

            if (is_string($pcks)) {
                return $this->errorResponse($pcks, 422);
            }

            foreach ($pcks as $pck) {
                $str_wheel_picked_balls = implode(",", $pck["balls"]);
                $str_wheel_picked_extras = implode(",", $pck["extras"]);

                $cart_subscriptions_picks = new CartSubscriptionPick();
                $cart_subscriptions_picks->cts_id = $cart_subscription->cts_id;

                $cart_subscriptions_picks->cts_wheel_picked_balls = $str_wheel_picked_balls;

                $cart_subscriptions_picks->cts_wheel_picked_extras = $str_wheel_picked_extras;

                $cart_subscriptions_picks->save();
            }
        }


        $cart = $cart_subscription->cart;
        $cart->crt_total += $cart_subscription->cts_price;
        $this->cartAmounts($cart);
        $request->merge(['pixel' => $cart->cart_step1()]);
        return $this->showOne($cart, 201);
    }


    /**
     * @SWG\Delete(
     *   path="/cart_lottery_wheels/{cart_lottery_wheel}",
     *   summary="Delete Cart Lottery details ",
     *   tags={"Cart Wheels"},
     *   @SWG\Parameter(
     *     name="cart_lottery_wheel",
     *     in="path",
     *     description="Cart Lottery Wheel Id.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="crt_id",
     *     in="formData",
     *     description="Cart crt_id",
     *     required=true,
     *     type="integer"
     *   ),
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=201,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(property="data", allOf={ @SWG\Schema(ref="#/definitions/Cart"), }),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function destroy($id, CartLotteryWheelDeleteRequest $request) {
        try{

            $validation = $this->validateCart($request->crt_id);
            if ($validation) return $validation;
            $lock = $this->check_for_cart_lock($request->crt_id);
            if ($lock) return $lock;
            $cart_subscription =  CartSubscription::where("crt_id", "=", $request->crt_id)
                ->where("cts_id", "=", $id)->first();

            if(!$cart_subscription){
                return $this->errorResponse(trans('lang.no_lottery_subscription'), 422);
            }

            $cart_subscription->cart_subscription_picks->each(function (CartSubscriptionPick $item) {
                $item->delete();
            });
            $cart = $cart_subscription->cart;
            $cart->crt_total -= $cart_subscription->cts_price;
            $cart_subscription->delete();
            $this->cartAmounts($cart);
            $request = request();
            $request->merge(['pixel' => $cart->cart_step1()]);
            return $this->showOne($cart);

        }catch (\Exception $ex){
            return $this->errorResponse(trans('lang.cart_valid'), 422);
        }

    }
}
