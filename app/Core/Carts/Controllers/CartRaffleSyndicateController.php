<?php

namespace App\Core\Carts\Controllers;

use App\Core\Carts\Models\Cart;
use App\Core\Carts\Models\CartRaffleSyndicate;
use App\Core\AdminLang\Services\AL;
use App\Core\Base\Services\ClientService;
use App\Core\Base\Services\FastTrackLogService;
use App\Core\Syndicates\Models\SyndicateRaffle;
use App\Core\Syndicates\Models\SyndicateRafflePrice;
use App\Core\Syndicates\Models\SyndicateRaffleSubscription;
use App\Core\Base\Traits\CartUtils;
use App\Core\Carts\Transforms\CartRaffleSyndicateTransformer;
use App\Core\Carts\Transforms\CartRaffleSyndicateTransformer2;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use DB;

class CartRaffleSyndicateController extends ApiController
{
    use CartUtils;

    public function __construct() {
        parent::__construct();
        $this->middleware('client.credentials')->except('index', 'details');
        $this->middleware('auth:api')->only('index', 'details');
        $this->middleware('transform.input:' . CartRaffleSyndicateTransformer::class)->only(['store', 'update']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @SWG\Get(
     *   path="/syndicate_raffle_subscriptions",
     *   summary="Show user raffles syndicate subscriptions",
     *   tags={"Subscriptions"},
     *   @SWG\Parameter(
     *     name="status",
     *     in="query",
     *     description="Subscription status (active, expired)",
     *     required=false,
     *     type="string"
     *   ),
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/SyndicateRaffleSubscription")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function index() {
        $user = request()->user();
        $active_ids = DB::connection('mysql_external')->select('SELECT cts_id
			FROM syndicate_raffle_subscriptions s
				INNER JOIN raffle_info l ON s.inf_id = l.inf_id
				INNER JOIN syndicate_cart_raffles cs ON s.rsyndicate_cts_id = cs.cts_id
				INNER JOIN syndicate_raffle sy ON cs.rsyndicate_id = sy.id
				INNER JOIN carts c ON c.crt_id = cs.crt_id
				LEFT JOIN raffles dw ON (s.sub_lastdraw_id=dw.rff_id)
			WHERE s.usr_id = '.$user->usr_id.'
				AND ((sub_tickets+sub_ticket_extra > sub_emitted) OR (s.sub_lastdraw_id>0 AND dw.rff_status=1))
				AND s.sub_status != 2
			ORDER BY s.rsyndicate_sub_id desc');
        $ids = [];
        foreach ($active_ids as $id) {
            $ids [] = $id->cts_id;
        }
        $active = CartRaffleSyndicate::with(['cart', 'syndicate_raffle_subscriptions.syndicate_raffle_prizes', 'syndicate_raffle_subscriptions.raffle', 'syndicate_raffle_subscriptions.last_draw', 'syndicate_raffle_subscriptions.syndicate_raffle_participations', 'syndicate_raffle'])
            ->whereIn('cts_id', $ids)
            ->get();
        $parents = DB::connection('mysql_external')->select('SELECT sub_parent FROM syndicate_raffle_subscriptions WHERE usr_id = '.$user->usr_id.' and sub_buydate <= DATE_SUB(NOW(), interval 60 DAY ) and sub_parent <> 0 ');
        $parent_in = '';
        foreach ($parents as $parent) {
            $parent_in .= ',' . $parent->sub_parent;
        }

        if ($parent_in != '') {
            $parent_where = "AND s.rsyndicate_sub_id not in (" . substr($parent_in, 1) . ")";
        } else {
            $parent_where = "";
        }
        $inactive_ids = DB::connection('mysql_external')->select('SELECT cts_id
            FROM syndicate_raffle_subscriptions s
                INNER JOIN raffle_info l ON s.inf_id = l.inf_id
                INNER JOIN syndicate_cart_raffles cs ON s.rsyndicate_cts_id = cs.cts_id
                INNER JOIN syndicate_raffle sy ON cs.rsyndicate_id = sy.id
                INNER JOIN carts c ON c.crt_id = cs.crt_id
                INNER JOIN raffles dw ON (s.sub_lastdraw_id=dw.rff_id)
                INNER JOIN regions r ON dw.rff_region = r.reg_id
            WHERE s.usr_id ='.$user->usr_id.'
                AND ((sub_tickets+sub_ticket_extra-sub_emitted)=0 or s.sub_status = 2)
            AND rff_status != 1 '.
            $parent_where.'
            ORDER BY s.rsyndicate_sub_id desc limit '.config('constants.inactive_qty'));
        $ids = [];
        foreach ($inactive_ids as $id) {
            $ids [] = $id->cts_id;
        }
        $inactive = CartRaffleSyndicate::with(['cart', 'syndicate_raffle_subscriptions.syndicate_raffle_prizes', 'syndicate_raffle_subscriptions.raffle', 'syndicate_raffle_subscriptions.last_draw', 'syndicate_raffle_subscriptions.syndicate_raffle_participations', 'syndicate_raffle'])
            ->whereIn('cts_id', $ids)
            ->get();
        $raffle_syndicate_subscriptions = $active->concat($inactive)->sortByDesc('purchase_date');
        if($raffle_syndicate_subscriptions->isNotEmpty()) {
            $raffle_syndicate_subscriptions->first()->transformer = CartRaffleSyndicateTransformer2::class;
        }
        return $this->showAllNoPaginated($raffle_syndicate_subscriptions);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    /**
     * @SWG\Post(
     *   path="/cart_raffle_syndicates",
     *   summary="Create Cart Raffle",
     *   tags={"Cart Raffles Syndicates"},
     *   consumes={"multipart/form-data"},
     *   @SWG\Parameter(
     *     name="cart_id",
     *     in="formData",
     *     description="Cart Id.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="raffle_syndicate_id",
     *     in="formData",
     *     description="Raffle Syndicate Id.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="tickets",
     *     in="formData",
     *     description="Tickets",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="price_id",
     *     in="formData",
     *     description="Price Id.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="syndicate_subscription_id",
     *     in="formData",
     *     description="Raffle Syndicate Subscription Parent Id.",
     *     required=false,
     *     type="integer"
     *   ),
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{}}
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
    public function store(Request $request) {
        $rules = [
            'crt_id' => 'required|integer|exists:mysql_external.carts',
            'rsyndicate_id' => 'required|integer|exists:mysql_external.syndicate_raffle,id',
            'rsub_id' => 'integer|exists:mysql_external.syndicate_raffle_subscriptions,rsyndicate_sub_id',
            'cts_ticket_byDraw' => 'required|integer|min:1|max:10',
            'cts_syndicate_prc_id' => 'required|integer|exists:mysql_external.syndicate_raffle_prices,prc_id',
        ];
        $this->validate($request, $rules);
        $validation = $this->validateCart($request->crt_id);
        if ($validation) return $validation;
        $lock = $this->check_for_cart_lock($request->crt_id);
        if ($lock) return $lock;

        $is_orca = ClientService::isOrca();
        $raffle_syndicate = SyndicateRaffle::find($request->rsyndicate_id);

        if (!$raffle_syndicate) $this->errorResponse(trans('lang.raffle__syndicate_forbidden'), 403);
        if (!$raffle_syndicate->isActive()) $this->errorResponse(trans('lang.raffle_syndicate_inactive'), 403);

        $pick_config = Config::get('constants.raffle_syndicate_picks');
        $syndicate_raffle_pick = isset($pick_config[$request->rsyndicate_id]) ? $pick_config[$request->rsyndicate_id] : 0;

        $price_id = $request->cts_syndicate_prc_id;
        $prices = $raffle_syndicate->syndicate_raffle_prices->pluck('prc_id');

        $raffle_syndicate_price = $is_orca ? SyndicateRafflePrice::where('prc_id', $price_id)->first() :
            SyndicateRafflePrice::where('prc_id', $price_id)->whereIn('prc_id', $prices)->first();
        if (!$raffle_syndicate_price) return $this->errorResponse(trans('lang.raffle_syndicate_price_invalid'), 422);

        $price = $raffle_syndicate_price ? $raffle_syndicate_price->price * $request->cts_ticket_byDraw : 0;

        $cart_raffle_syndicate = new CartRaffleSyndicate();
        $cart_raffle_syndicate->crt_id = $request->crt_id;
        $cart_raffle_syndicate->rsyndicate_id = $request->rsyndicate_id;
        $cart_raffle_syndicate->cts_price = $price;
        $cart_raffle_syndicate->rsub_id = $request->rsub_id ? $request->rsub_id : 0;
        $cart_raffle_syndicate->cts_ticket_extra = 0;
        $cart_raffle_syndicate->cts_ticket_byDraw = $request->cts_ticket_byDraw;
        $cart_raffle_syndicate->cts_ticket_nextDraw = $request->cts_ticket_byDraw;
        $cart_raffle_syndicate->cts_renew = $raffle_syndicate->no_renew;
        $cart_raffle_syndicate->cts_syndicate_prc_id = $request->cts_syndicate_prc_id;
        $cart_raffle_syndicate->cts_play_same_group= 1;
        $cart_raffle_syndicate->rsyndicate_picks_id = $syndicate_raffle_pick;
        $cart_raffle_syndicate->bonus_id = 0;
        $cart_raffle_syndicate->save();
        $cart = $cart_raffle_syndicate->cart;
        $cart->crt_total += $cart_raffle_syndicate->cts_price;
        $this->cartAmounts($cart);
        $request->merge(['pixel' => $cart->cart_step1()]);
        return $this->showOne($cart, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Core\Carts\Models\CartRaffle $cartRaffle
     * @return \Illuminate\Http\Response
     */
    /**
     * @SWG\Get(
     *   path="/cart_raffle_syndicates/{cart_raffle_syndicate}",
     *   summary="Show Cart Raffle Syndicate details ",
     *   tags={"Cart Raffles Syndicates"},
     *   @SWG\Parameter(
     *     name="cart_raffle_syndicate",
     *     in="path",
     *     description="Cart Raffle Syndicate Id.",
     *     required=true,
     *     type="integer"
     *   ),
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{}}
     *   },
     *
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(property="data", allOf={ @SWG\Schema(ref="#/definitions/CartRaffle"), }),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function show(CartRaffleSyndicate $cart_raffle_syndicate) {
        $validation = $this->validateCart($cart_raffle_syndicate->crt_id);
        if ($validation) return $validation;
        return $this->showOne($cart_raffle_syndicate);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request                   $request
     * @param  \App\Core\Carts\Models\CartRaffleSyndicate $cart_raffle_syndicate
     * @return \Illuminate\Http\Response
     */
    /**
     * @SWG\Put(
     *   path="/cart_raffle_syndicates/{cart_raffle_syndicate}",
     *   summary="Update Raffle Syndicate Cart",
     *   tags={"Cart Raffles Syndicates"},
     *   consumes={"application/x-www-form-urlencoded"},
     *   @SWG\Parameter(
     *     name="cart_raffle_syndicate",
     *     in="path",
     *     description="Cart Raffle Syndicate Id.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="price_id",
     *     in="formData",
     *     description="Price Id.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="tickets",
     *     in="formData",
     *     description="Tickets by Draw.",
     *     required=true,
     *     type="integer"
     *   ),
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{}}
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
    public function update(Request $request, CartRaffleSyndicate $cart_raffle_syndicate) {
        $rules = [
            'cts_ticket_byDraw' => 'required|integer|min:1|max:10',
            'cts_syndicate_prc_id' => 'required|integer|exists:mysql_external.syndicate_raffle_prices,prc_id',
        ];
        $this->validate($request, $rules);

        $validation = $this->validateCart($cart_raffle_syndicate->crt_id);
        if ($validation) return $validation;
        $lock = $this->check_for_cart_lock($cart_raffle_syndicate->crt_id);
        if ($lock) return $lock;
        $raffle_syndicate = SyndicateRaffle::find($cart_raffle_syndicate->rsyndicate_id);

        if (!$raffle_syndicate) $this->errorResponse(trans('lang.raffle__syndicate_forbidden'), 403);
        if (!$raffle_syndicate->isActive()) $this->errorResponse(trans('lang.raffle_syndicate_inactive'), 403);

        $price_id = $request->cts_syndicate_prc_id ? $request->cts_syndicate_prc_id : $cart_raffle_syndicate->cts_syndicate_prc_id;
        $prices = $raffle_syndicate->syndicate_raffle_prices->pluck('prc_id');
        $raffle_syndicate_price = SyndicateRafflePrice::where('prc_id', $price_id)->whereIn('prc_id', $prices)->first();
        if (!$raffle_syndicate_price) return $this->errorResponse(trans('lang.raffle_syndicate_price_invalid'), 422);

        $price = $raffle_syndicate_price ? $raffle_syndicate_price->price * $request->cts_ticket_byDraw : 0;

        $cart = $cart_raffle_syndicate->cart;
        $cart->crt_total -= $cart_raffle_syndicate->cts_price;

        $cart_raffle_syndicate->cts_price = $price;
        $cart_raffle_syndicate->cts_ticket_byDraw = $request->cts_ticket_byDraw;
        $cart_raffle_syndicate->cts_ticket_nextDraw = $request->cts_ticket_byDraw;
        $cart_raffle_syndicate->cts_syndicate_prc_id = $request->cts_syndicate_prc_id;
        $cart_raffle_syndicate->save();
        $cart->crt_total += $cart_raffle_syndicate->cts_price;
        $this->cartAmounts($cart);
        $request = request();
        $request->merge(['pixel' => $cart->cart_step1()]);
        return $this->showOne($cart);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Core\Carts\Models\CartRaffle $cart_raffle
     * @return \Illuminate\Http\Response
     */
    /**
     * @SWG\Delete(
     *   path="/cart_raffle_syndicates/{cart_raffle_syndicate}",
     *   summary="Delete Cart Raffle Syndicate",
     *   tags={"Cart Raffles Syndicates"},
     *   @SWG\Parameter(
     *     name="cart_raffle_syndicate",
     *     in="path",
     *     description="Cart Raffle Syndicate Id.",
     *     required=true,
     *     type="integer"
     *   ),
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{}}
     *   },
     *
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(property="data", allOf={ @SWG\Schema(ref="#/definitions/Cart"), }),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function destroy(CartRaffleSyndicate $cart_raffle_syndicate) {
        $validation = $this->validateCart($cart_raffle_syndicate->crt_id);
        if ($validation) return $validation;
        $lock = $this->check_for_cart_lock($cart_raffle_syndicate->crt_id);
        if ($lock) return $lock;
        $cart = $cart_raffle_syndicate->cart;
        $cart->crt_total -= $cart_raffle_syndicate->cts_price;
        $cart_raffle_syndicate->delete();
        $this->cartAmounts($cart);
        $request = request();
        $request->merge(['pixel' => $cart->cart_step1()]);
        return $this->showOne($cart);
    }

    /**
     *   @SWG\Definition(
     *     definition="RaffleSyndicateSubscriptionDetail",
     *     required={"identifier"},
     *     @SWG\Property(
     *       property="identifier",
     *       type="integer",
     *       format="int32",
     *       description="ID elements identifier",
     *       example="305"
     *     ),
     *     @SWG\Property(
     *       property="order",
     *       type="integer",
     *       format="int32",
     *       description="Cart ID",
     *       example="305"
     *     ),
     *     @SWG\Property(
     *       property="status",
     *       description="Subscription status",
     *       type="string",
     *       example="active"
     *     ),
     *     @SWG\Property(
     *       property="status_tag",
     *       description="Subscription status tag",
     *       type="string",
     *       example="#SUBSCRIPTION_DETAIL_STATUS_ACTIVE#"
     *     ),
     *     @SWG\Property(
     *       property="raffle_syndicate_identifier",
     *       type="integer",
     *       description="Raffle Syndicate Id",
     *       example="111"
     *     ),
     *     @SWG\Property(
     *       property="raffle_syndicate_name",
     *       type="string",
     *       description="Raffle Syndicate name",
     *       example="#GROUP_SORTEO_GORDITO#"
     *     ),
     *     @SWG\Property(
     *       property="purchase_date",
     *       description="Purchase date",
     *       type="string",
     *       format="date-time",
     *       example="2014-06-05 09:57:55"
     *     ),
     *     @SWG\Property(
     *       property="subscriptions",
     *       description="Subscriptions quantity",
     *       type="integer",
     *       example="1"
     *     ),
     *     @SWG\Property(
     *       property="prize",
     *       description="Prize",
     *       type="number",
     *       format="float",
     *       example="0.25"
     *     ),
     *     @SWG\Property(
     *       property="extra_tickets",
     *       description="Extra tickets",
     *       type="integer",
     *       example="2"
     *     ),
     *     @SWG\Property(
     *       property="draw_date",
     *       description="Draw date",
     *       type="string",
     *       format="date_time",
     *       example="2013-07-26 06:50:06"
     *      ),
     *     @SWG\Property(
     *       property="draws",
     *       description="Draws",
     *       type="array",
     *       @SWG\Items(
     *         @SWG\Property(property="emited", type="integer", description="Emitted", example="1"),
     *         @SWG\Property(property="total", type="integer", format="integer", description="Total", example="2"),
     *       ),
     *     ),
     *     @SWG\Property(
     *       property="raffle_syndicate_subscriptions",
     *       description="Raffle Syndicate Subscriptions",
     *       type="array",
     *       @SWG\Items(ref="#/definitions/RaffleSyndicateSubscription"),
     *     ),
     *   ),
     */

    /**
     * @SWG\Get(
     *   path="/syndicate_raffle_subscriptions/details/{syndicate_raffle_subscription}",
     *   summary="Show Cart Raffle Syndicate details ",
     *   tags={"Subscriptions"},
     *   @SWG\Parameter(
     *     name="syndicate_raffle_subscription",
     *     in="path",
     *     description="Raffle Syndicate Subscription Id.",
     *     required=true,
     *     type="integer"
     *   ),
     *   security={
     *     {"password": {}, "user_ip":{}}
     *   },
     *
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(property="data", allOf={ @SWG\Schema(ref="#/definitions/RaffleSyndicateSubscriptionDetail"), }),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */


    public function details(CartRaffleSyndicate $cart_raffle_syndicate) {
        $user_id = Auth::user() ? Auth::user()->usr_id : 0;
        $cart_user_id = $cart_raffle_syndicate->cart ? $cart_raffle_syndicate->cart->usr_id : null;
        if ($user_id != $cart_user_id) {
            return $this->errorResponse(trans('lang.syndicate_subscription_forbidden'), 422);
        }

        $syndicate_raffle = $cart_raffle_syndicate->syndicate_raffle;
        $descriptor = $cart_raffle_syndicate->cart ? $cart_raffle_syndicate->cart->crt_descriptor : null;
        $raffleSyndicate = $cart_raffle_syndicate->syndicate_raffle;
        $currency = '';
        if(!is_null($raffleSyndicate)){
           $currency = $raffleSyndicate->currency;
        }
        $result = [
            'identifier' => $cart_raffle_syndicate->cts_id,
            'order' => $cart_raffle_syndicate->crt_id,
            'status' => $cart_raffle_syndicate->status,
            'status_tag' => $cart_raffle_syndicate->status_tag,
            'raffle_syndicate_identifier' => $cart_raffle_syndicate->rsyndicate_id,
            'raffle_syndicate_name' => $syndicate_raffle ? AL::translate(str_replace("#", "", $syndicate_raffle->syndicate_raffle_name)) : null,
            'purchase_date' => (string)$cart_raffle_syndicate->purchase_date,
            'subscriptions' => $cart_raffle_syndicate->subscriptions,
            'prize' => $cart_raffle_syndicate->prizes,
            'currency' => $currency,
            'draw_date' => $cart_raffle_syndicate->syndicate_raffle->date,
        ];
        if ($syndicate_raffle && $syndicate_raffle->multi_raffle == 0) {
            $syndicate_raffle_subscription = $cart_raffle_syndicate->syndicate_raffle_subscriptions_list()->isNotEmpty() ?
                $cart_raffle_syndicate->syndicate_raffle_subscriptions_list()->first() : null;
            $result['extra_tickets'] = $syndicate_raffle_subscription ? $syndicate_raffle_subscription->sub_ticket_extra : null;
            $result['draws'] = $syndicate_raffle_subscription ? $syndicate_raffle_subscription->draws : null;
        }
        if ($descriptor && $descriptor != '') {
            $result['descriptor'] = $descriptor;
        }

        $raffle_subscriptions = collect([]);
        $cart_raffle_syndicate->syndicate_raffle_subscriptions->each(function (SyndicateRaffleSubscription $item) use ($raffle_subscriptions) {
            $raffle_subscriptions->push($item->transformer::transform($item));
        });
        $result['raffle_syndicate_subscriptions'] = $raffle_subscriptions;
        return $this->successResponse(['data' => $result]);


    }
}
