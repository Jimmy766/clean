<?php

namespace App\Core\Carts\Controllers;

use App\Core\Carts\Models\Cart;
use App\Core\Base\Services\ClientService;
use App\Core\Base\Services\FastTrackLogService;
use App\Core\Syndicates\Models\Syndicate;
use App\Core\Syndicates\Models\SyndicateCartSubscription;
use App\Core\Syndicates\Models\SyndicatePrice;
use App\Core\Syndicates\Models\SyndicateSubscription;
use App\Core\Base\Traits\CartUtils;
use App\Core\Syndicates\Transforms\SyndicateCartSubscriptionTransformer;
use App\Core\Syndicates\Transforms\SyndicateCartSubscriptionTransformer2;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;

class SyndicateCartSubscriptionController extends ApiController
{

    use CartUtils;

    /**
     * CartSubscriptionController constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->middleware('client.credentials')->except('index', 'details');
        $this->middleware('auth:api')->only('index', 'details');
        $this->middleware('transform.input:' . SyndicateCartSubscriptionTransformer::class)->only(['store', 'update']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @SWG\Get(
     *   path="/lottery_syndicate_subscriptions",
     *   summary="Show user syndicate subscriptions",
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
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/CartSyndicateSubscription2")),
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
			FROM syndicate_subscriptions s
				INNER JOIN lotteries l on s.lot_id = l.lot_id
				INNER JOIN regions r on l.lot_region_country = r.reg_id
				INNER JOIN syndicate_cart_subscriptions cs ON cs.cts_id = s.syndicate_cts_id
				INNER JOIN syndicate sy ON cs.syndicate_id = sy.id
				INNER JOIN carts c ON c.crt_id = cs.crt_id
				LEFT JOIN draws dw ON (s.sub_lastdraw_id=dw.draw_id)
			WHERE s.usr_id = ' . $user->usr_id . '
				AND ((sub_tickets+sub_ticket_extra > sub_emitted) or (s.sub_lastdraw_id>0 and dw.draw_status IN(0,2)))
				AND s.sub_status != 2 ');
        $ids = [];
        foreach ($active_ids as $id) {
            $ids [] = $id->cts_id;
        }
        $active = SyndicateCartSubscription::with(['cart', 'syndicate_subscriptions.syndicate', 'syndicate_subscriptions.syndicate_participations', 'syndicate_subscriptions.sindicate_prizes', 'syndicate_subscriptions.last_draw', 'syndicate.syndicate_lotteries.lottery.draws.lottery', 'bonus'])
            ->whereIn('cts_id', $ids)
            ->get();

        $parents = DB::connection('mysql_external')->select('SELECT sub_parent FROM syndicate_subscriptions WHERE usr_id = ' . $user->usr_id . ' and sub_buydate <= DATE_SUB(NOW(), interval 60 DAY) and sub_parent <> 0 ');
        $parent_in = '';
        foreach ($parents as $parent) {
            $parent_in .= ',' . $parent->sub_parent;
        }

        if ($parent_in != '') {
            $parent_where = "AND s.syndicate_sub_id not in (" . substr($parent_in, 1) . ")";
        } else {
            $parent_where = "";
        }
        $inactive_ids = DB::connection('mysql_external')->select('SELECT cts_id
            FROM syndicate_subscriptions s
                INNER JOIN lotteries l ON s.lot_id = l.lot_id
                INNER JOIN regions r ON l.lot_region_country = r.reg_id
                INNER JOIN syndicate_cart_subscriptions cs ON cs.cts_id = s.syndicate_cts_id
                INNER JOIN syndicate sy ON cs.syndicate_id = sy.id
                INNER JOIN carts c ON c.crt_id = cs.crt_id
                INNER JOIN draws dw ON (s.sub_lastdraw_id=dw.draw_id)
            WHERE s.usr_id = ' . $user->usr_id . '
                AND ((sub_tickets+sub_ticket_extra-sub_emitted)=0 or s.sub_status = 2)
            AND draw_status NOT IN(0,2) ' .
            $parent_where .' ORDER BY s.sub_buydate desc limit '.config('constants.inactive_qty'));
        $ids = [];
        foreach ($inactive_ids as $id) {
            $ids [] = $id->cts_id;
        }
        $inactive = SyndicateCartSubscription::with(['cart', 'syndicate_subscriptions.syndicate', 'syndicate_subscriptions.syndicate_participations', 'syndicate_subscriptions.sindicate_prizes', 'syndicate_subscriptions.last_draw', 'syndicate.syndicate_lotteries.lottery.draws.lottery', 'bonus'])
            ->whereIn('cts_id', $ids)
            ->get();
        $syndicate_subscriptions = $active->concat($inactive)->sortByDesc('purchase_date');
        if($syndicate_subscriptions->isNotEmpty()) {
            $syndicate_subscriptions->first()->transformer = SyndicateCartSubscriptionTransformer2::class;
        }

        return $this->showAllNoPaginated($syndicate_subscriptions);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    /**
     * @SWG\Post(
     *   path="/cart_syndicates",
     *   summary="Create Syndicate Cart Lottery",
     *   tags={"Cart Syndicates"},
     *   consumes={"multipart/form-data"},
     *   @SWG\Parameter(
     *     name="cart_id",
     *     in="formData",
     *     description="Cart Id.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="syndicate_id",
     *     in="formData",
     *     description="Syndicate Id.",
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
     *     name="tickets_by_draw",
     *     in="formData",
     *     description="Tickets by Draw.",
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

    public function store(Request $request) {
        $rules = [
            'crt_id' => 'required|integer|exists:mysql_external.carts',
            'syndicate_id' => 'required|integer|exists:mysql_external.syndicate,id',
            'cts_syndicate_prc_id' => 'required|integer|exists:mysql_external.syndicate_prices,prc_id',
            'cts_ticket_byDraw' => 'required|integer|min:1|max:10',
            //'bonus_id' => 'integer|exists:mysql_external.bonuses,id',
        ];
        $this->validate($request, $rules);
        $validation = $this->validateCart($request->crt_id);
        if ($validation)
            return $validation;
        $lock = $this->check_for_cart_lock($request->crt_id);
        if ($lock)
            return $lock;

        $is_orca = ClientService::isOrca();
        $syndicate = Syndicate::where('id', '=', $request->syndicate_id)->where('active', '=', 1)
            ->whereIn('id', self::client_syndicates(1)->pluck('product_id'))->first();
        if (!$syndicate)
            return $this->errorResponse(trans('lang.syndicate_forbidden'), 403);
        $price_id = $request->cts_syndicate_prc_id;
        $prices = $syndicate->syndicate_prices->pluck('prc_id');
        $syndicate_price = $is_orca ? SyndicatePrice::where('prc_id', $price_id)->first() :
            SyndicatePrice::where('prc_id', $price_id)->whereIn('prc_id', $prices)->first();
        if (!$syndicate_price)
            return $this->errorResponse(trans('lang.syndicate_price_invalid'), 422);
        $syndicate_cart_subscription = new SyndicateCartSubscription();
        $syndicate_cart_subscription->crt_id = $request->crt_id;
        $syndicate_cart_subscription->syndicate_id = $request->syndicate_id;
        $syndicate_cart_subscription->cts_price = $request->cts_ticket_byDraw * $syndicate_price->syndicate_price_line['price'];
        $syndicate_cart_subscription->sub_id = 0;
        $syndicate_cart_subscription->cts_ticket_extra = 0;
        $syndicate_cart_subscription->cts_ticket_byDraw = $request->cts_ticket_byDraw;
        $syndicate_cart_subscription->cts_ticket_nextDraw = $request->cts_ticket_byDraw;

        /** Si no es renovable, no es renovable, de lo contrario me puedo fijar si llega algo */
        $syndicate_cart_subscription->cts_renew = $syndicate->no_renew ? $syndicate->no_renew :
            ($request->has("cts_renew") && $request->get("cts_renew") ?
                $request->get("cts_renew") : $syndicate->no_renew );

        $syndicate_cart_subscription->cts_syndicate_prc_id = $request->cts_syndicate_prc_id;
        $syndicate_cart_subscription->syndicate_picks_id = 0;
        //$syndicate_cart_subscription->bonus_id = $request->bonus_id ?? 0;
        $syndicate_cart_subscription->bonus_id = 0;
        $syndicate_cart_subscription->save();
        $cart = $syndicate_cart_subscription->cart;
        $cart->crt_total += $syndicate_cart_subscription->cts_price;
        $this->cartAmounts($cart);
        $request->merge(['pixel' => $cart->cart_step1()]);
        return $this->showOne($cart);
    }

    /**
     * @SWG\Get(
     *   path="/cart_syndicates/{cart_syndicate}",
     *   summary="Show Cart Syndicate details ",
     *   tags={"Cart Syndicates"},
     *   @SWG\Parameter(
     *     name="cart_syndicate",
     *     in="path",
     *     description="Cart Syndicate Id.",
     *     required=true,
     *     type="integer"
     *   ),
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(property="data", allOf={ @SWG\Schema(ref="#/definitions/CartSyndicateSubscription"), }),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    /**
     * Display the specified resource.
     *
     * @param  \App\Core\Syndicates\Models\SyndicateCartSubscription $syndicate_cart_subscription
     * @return \Illuminate\Http\Response
     */
    public function show(SyndicateCartSubscription $syndicate_cart_subscription) {
        $validation = $this->validateCart($syndicate_cart_subscription->crt_id);
        if ($validation)
            return $validation;
        return $this->showOne($syndicate_cart_subscription);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request                              $request
     * @param  \App\Core\Syndicates\Models\SyndicateCartSubscription $syndicate_cart_subscription
     * @return \Illuminate\Http\Response
     */
    /**
     * @SWG\Put(
     *   path="/cart_syndicates/{cart_syndicate}",
     *   summary="Update Syndicate Cart Lottery",
     *   tags={"Cart Syndicates"},
     *   consumes={"application/x-www-form-urlencoded"},
     *   @SWG\Parameter(
     *     name="cart_syndicate",
     *     in="path",
     *     description="Cart Syndicate Id.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="price_id",
     *     in="formData",
     *     description="Price Id.",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="tickets_by_draw",
     *     in="formData",
     *     description="Tickets by Draw.",
     *     required=false,
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
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function update(Request $request, SyndicateCartSubscription $syndicate_cart_subscription) {
        $rules = [
            'cts_syndicate_prc_id' => 'integer|exists:mysql_external.syndicate_prices,prc_id',
            'cts_ticket_byDraw' => 'integer|min:1|max:10',
        ];
        $this->validate($request, $rules);
        $validation = $this->validateCart($syndicate_cart_subscription->crt_id);
        if ($validation)
            return $validation;
        $lock = $this->check_for_cart_lock($syndicate_cart_subscription->crt_id);
        if ($lock)
            return $lock;
        //if ($syndicate_cart_subscription->bonus_id != 0) return $this->errorResponse(trans('lang.cart_syndicate_update'), 403);

        $syndicate = Syndicate::where('id', '=', $syndicate_cart_subscription->syndicate_id)->where('active', '=', 1)
            ->whereIn('id', self::client_syndicates(1)->pluck('product_id'))->first();
        if (!$syndicate)
            $this->errorResponse(trans('lang.syndicate_forbidden'), 403);

        $price_id = $request->cts_syndicate_prc_id ? $request->cts_syndicate_prc_id : $syndicate_cart_subscription->cts_syndicate_prc_id;
        $prices = $syndicate->syndicate_prices->pluck('prc_id');
        $syndicate_price = SyndicatePrice::where('prc_id', $price_id)->whereIn('prc_id', $prices)->first();
        if (!$syndicate_price)
            return $this->errorResponse(trans('lang.syndicate_price_invalid'), 422);

        $tickets_by_draw = $request->cts_ticket_byDraw ? $request->cts_ticket_byDraw : $syndicate_cart_subscription->cts_ticket_byDraw;

        $cart = $syndicate_cart_subscription->cart;
        $cart->crt_total -= $syndicate_cart_subscription->cts_price;
        $syndicate_cart_subscription->cts_price = $tickets_by_draw * $syndicate_price->syndicate_price_line['price'];
        $syndicate_cart_subscription->cts_ticket_byDraw = $tickets_by_draw;
        $syndicate_cart_subscription->cts_ticket_nextDraw = $tickets_by_draw;
        $syndicate_cart_subscription->cts_syndicate_prc_id = $price_id;
        $syndicate_cart_subscription->save();
        $cart->crt_total += $syndicate_cart_subscription->cts_price;
        $this->cartAmounts($cart);
        $request->merge(['pixel' => $cart->cart_step1()]);
        return $this->showOne($cart);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Core\Syndicates\Models\SyndicateCartSubscription $syndicate_cart_subscription
     * @return \Illuminate\Http\Response
     */
    /**
     * @SWG\Delete(
     *   path="/cart_syndicates/{cart_syndicate}",
     *   summary="Delete Cart Syndicate",
     *   tags={"Cart Syndicates"},
     *   @SWG\Parameter(
     *     name="cart_syndicate",
     *     in="path",
     *     description="Cart Syndicate Id.",
     *     required=true,
     *     type="integer"
     *   ),
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
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
    public function destroy(SyndicateCartSubscription $syndicate_cart_subscription) {
        $validation = $this->validateCart($syndicate_cart_subscription->crt_id);
        if ($validation)
            return $validation;
        $lock = $this->check_for_cart_lock($syndicate_cart_subscription->crt_id);
        if ($lock)
            return $lock;
        $cart = $syndicate_cart_subscription->cart;
        $cart->crt_total -= $syndicate_cart_subscription->cts_price;
        $syndicate_cart_subscription->delete();
        $this->cartAmounts($cart);
        $request = request();
        $request->merge(['pixel' => $cart->cart_step1()]);
        return $this->showOne($cart);
    }

    /**
     * @SWG\Definition(
     *     definition="LotterySyndicateSubscriptionDetail",
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
     *       property="syndicate_identifier",
     *       type="integer",
     *       description="Syndicate Id",
     *       example="111"
     *     ),
     *     @SWG\Property(
     *       property="syndicate_name",
     *       type="string",
     *       description="Syndicate name",
     *       example="EuroMillions Star Syndicate"
     *     ),
     *     @SWG\Property(
     *       property="syndicate_tag_name",
     *       type="string",
     *       description="Syndicate tag name",
     *       example="#PLAY_GROUP_NAME_EUROMILLIONS_STAR#"
     *     ),
     *     @SWG\Property(
     *       property="syndicate_tag_name_short",
     *       type="string",
     *       description="Syndicate tag name short",
     *       example="#PLAY_GROUP_NAME_SHORT_EUROMILLIONS_STAR#"
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
     *       property="prizes",
     *       description="Prizes",
     *       type="array",
     *       @SWG\Items(
     *         @SWG\Property(property="currency", type="string", description="Currency", example="USD"),
     *         @SWG\Property(property="prize", type="number", format="float", description="Price", example="0.11"),
     *       ),
     *     ),
     *     @SWG\Property(
     *       property="syndicate_subscriptions",
     *       description="Syndicate Subscriptions",
     *       type="array",
     *       @SWG\Items(ref="#/definitions/SyndicateSubscription"),
     *     ),
     *   ),
     */

    /**
     * @SWG\Get(
     *   path="/lottery_syndicate_subscriptions/details/{lottery_syndicate_subscription}",
     *   summary="Show Cart Syndicate details ",
     *   tags={"Subscriptions"},
     *   @SWG\Parameter(
     *     name="lottery_syndicate_subscription",
     *     in="path",
     *     description="Lottery Syndicate Subscription Id.",
     *     required=true,
     *     type="integer"
     *   ),
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(property="data", allOf={ @SWG\Schema(ref="#/definitions/LotterySyndicateSubscriptionDetail"), }),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    /**
     * @param \App\Core\Syndicates\Models\SyndicateCartSubscription $syndicate_cart_subscription
     * @return array
     */
    public function details(SyndicateCartSubscription $syndicate_cart_subscription) {
        $user_id = Auth::user() ? Auth::user()->usr_id : 0;
        $cart_user_id = $syndicate_cart_subscription->cart ? $syndicate_cart_subscription->cart->usr_id : null;
        if ($user_id != $cart_user_id) {
            return $this->errorResponse(trans('lang.syndicate_subscription_forbidden'), 422);
        }
        $syndicate_subscriptions = $syndicate_cart_subscription->syndicate_subscriptions;
        /*$syndicate_subscriptions = $syndicate_subscriptions->filter(function ($item) {
            return ((!$item->last_draw && !$item->isActive()) || ($item->last_draw && !$item->IsExpired()))
                ? false : true;
        });
        if ($syndicate_subscriptions->isEmpty()) {
            return $this->errorResponse(trans('lang.syndicate_subscription_forbidden'), 422);
        }*/
        $syndicate = $syndicate_cart_subscription->syndicate ? $syndicate_cart_subscription->syndicate : null;
        $result = [
            'identifier' => $syndicate_cart_subscription->cts_id,
            'order' => $syndicate_cart_subscription->crt_id,
            'status' => $syndicate_cart_subscription->status,
            'status_tag' => $syndicate_cart_subscription->status_tag,
            'syndicate_identifier' => $syndicate_cart_subscription->syndicate_id,
            'syndicate_name' => $syndicate ? $syndicate->printable_name : null,
            'syndicate_tag_name' => $syndicate ? '#PLAY_GROUP_NAME_' . $syndicate->name . '#' : null,
            'syndicate_tag_name_short' => $syndicate ? '#PLAY_GROUP_NAME_SHORT_' . $syndicate->name . '#' : null,
            'purchase_date' => $syndicate_cart_subscription->purchase_date,
            'subscriptions' => $syndicate_cart_subscription->subscriptions,
            'prizes' => $syndicate_cart_subscription->prizes,
        ];

        if ($syndicate && $syndicate->multi_lotto == 0) {
            $syndicate_subscription = $syndicate_cart_subscription->syndicate_subscriptions_list()->isNotEmpty() ?
                $syndicate_cart_subscription->syndicate_subscriptions_list()->first() : null;
            $result['extra_tickets'] = $syndicate_subscription ? $syndicate_subscription->sub_ticket_extra : null;
            $result['draws'] = $syndicate_cart_subscription->draws;
            if ($syndicate_cart_subscription->cart && $syndicate_cart_subscription->cart->crt_descriptor != '') {
                $result['descriptor'] = $syndicate_cart_subscription->cart->crt_descriptor;
            }
        }
        $subscriptions = collect([]);

        $syndicate_cart_subscription->syndicate_subscriptions
            ->load("last_draw", "syndicate", "syndicate_lottery.region",
                "syndicate_participations.syndicate_prizes",
                "syndicate_participations.ticket_draw.lottery", "syndicate_participations.ticket_sub.draw.lottery",
                "syndicate_participations.ticket_sub.subscription.lottery",
                "sindicate_prizes");

        $total_draws = 0;
        $syndicate_cart_subscription->syndicate_subscriptions->each(function (SyndicateSubscription $item) use ($subscriptions,&$total_draws) {

            $sub_transformed = $item->transformer::transform($item);
            $total_draws += $sub_transformed['draws']['total'];
            $subscriptions->push($sub_transformed);


        });

        $result['total_draws'] = $total_draws;
        $result['syndicate_subscriptions'] = $subscriptions;

        return $this->successResponse(['data' => $result]);
    }
}
