<?php

namespace App\Core\Carts\Controllers;

use App\Core\Carts\Models\Cart;
use App\Core\Carts\Models\CartRaffle;
use App\Core\Raffles\Models\Raffle;
use App\Core\Raffles\Models\RafflePrice;
use App\Core\Raffles\Models\RaffleSubscription;
use App\Core\Base\Services\ClientService;
use App\Core\Base\Services\FastTrackLogService;
use App\Core\Base\Traits\CartUtils;
use App\Core\Carts\Transforms\CartRaffleTransformer;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;

class CartRaffleController extends ApiController
{
    use CartUtils;

    /**
     * CartRaffleController constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->middleware('client.credentials')->except('index', 'details');
        $this->middleware('auth:api')->only('index', 'details');
        $this->middleware('transform.input:' . CartRaffleTransformer::class)->only(['store']);
    }

    /**
     * @SWG\Get(
     *   path="/raffle_subscriptions",
     *   summary="Show user raffles subscriptions",
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
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/RaffleSubscription")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */

    public function index() {
        $inactive= RaffleSubscription::with(['cart_raffle', 'raffle.raffle_draws', 'raffle_tickets', 'last_draw'])
            ->where('usr_id', '=', request()->user()->usr_id)
            ->whereHas('last_draw', function ($query) {
                $query->where('rff_status', '!=', 1);
            })
            ->whereRaw('(((rsub_tickets + rsub_ticket_extra) = rsub_emitted) or rsub_status = 2)')
            ->orderBy('rsub_buydate', 'desc')
            ->limit(config('constants.inactive_qty'))
            ->get();
        $active= RaffleSubscription::with(['cart_raffle', 'raffle.raffle_draws', 'raffle_tickets', 'last_draw'])
            ->where('usr_id', '=', request()->user()->usr_id)
            ->where('rsub_status', '!=', 2)
            ->where(function ($query) {
                $query->whereRaw('(((rsub_tickets + rsub_ticket_extra) > rsub_emitted) or rsub_status = 2)')
                    ->orWhere(function ($query) {
                        $query->whereHas('last_draw', function ($query) {
                            $query->where('rff_status', '=', 1);
                        });
                    });
            })
            ->orderBy('rsub_buydate', 'desc')
            ->get();
        $raffles= $active->concat($inactive)->sortByDesc('rsub_buydate');
        return $this->showAllNoPaginated($raffles);
    }

    public function update(Request $request, CartRaffle $cart_raffle) {
        $rules = [
            'crf_prc_rff_id' => 'required|integer|exists:mysql_external.prices_raffles,prc_rff_id',
            'crf_tickets' => 'required|integer|min:1',
            'crf_renew' => 'integer|min:0|max:1',
            'crf_play_method' => 'required|integer|min:0|max:3'
        ];

        $this->validate($request, $rules);

        $validation = $this->validateCart($cart_raffle->crt_id);
        if ($validation) return $validation;


        $is_orca = ClientService::isOrca();

        $raffle = Raffle::where('inf_id', '=', $cart_raffle->inf_id)->first();

        $price_id = $request->crf_prc_rff_id;
        $prices = $raffle->raffle_prices->pluck('prc_rff_id');

        $raffle_price = $is_orca ? RafflePrice::where('prc_rff_id', $price_id)->first():
            RafflePrice::where('prc_rff_id', $price_id)->whereIn('prc_rff_id', $prices)->first();

        if (!$raffle_price) return $this->errorResponse(trans('lang.raffle_price_invalid'), 422);

        if ($raffle_price && $raffle_price->prc_rff_min_tickets > $request->crf_tickets) {
            return $this->errorResponse(trans('lang.raffle_min_tickets_invalid'), 422);
        }
        $prc_rff_draws = $raffle_price ? $raffle_price->prc_rff_draws : 0;
        $price = $raffle_price ? $raffle_price->price_line ? $raffle_price->price_line['price'] : 0 : 0;

        $cart = $cart_raffle->cart;
        $cart->crt_total -= $cart_raffle->crf_price;

        $cart_raffle->crf_tickets = $request->crf_tickets * $prc_rff_draws;
        $cart_raffle->crf_ticket_byDraw = $request->crf_tickets;
        $cart_raffle->crf_ticket_nextDraw = $request->crf_tickets;
        $cart_raffle->crf_price = $request->crf_tickets * $price;
        $cart_raffle->crf_play_method = $request->crf_play_method;
        $cart_raffle->crf_prc_rff_id = $request->crf_prc_rff_id;
        $cart_raffle->crf_renew = $request->has("crf_renew") ? $request->crf_renew : 1;
        $cart_raffle->save();

        $cart->crt_total += $request->crf_tickets * $price;

        $this->cartAmounts($cart);
        $request->merge(['pixel' => $cart->cart_step1()]);
        return $this->showOne($cart, 201);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    /**
     * @SWG\Post(
     *   path="/cart_raffles",
     *   summary="Create Cart Raffle",
     *   tags={"Cart Raffles"},
     *   consumes={"multipart/form-data"},
     *   @SWG\Parameter(
     *     name="cart_id",
     *     in="formData",
     *     description="Cart Id.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="raffle_id",
     *     in="formData",
     *     description="Raffle Id.",
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
     *     description="Tickets",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="play_method",
     *     in="formData",
     *     description="0 : random - Parts of different tickets, 1 : whole - Whole ticket, 2 : tenth - Parts of the same ticket",
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
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */

    public function store(Request $request) {
        $rules = [
            'crt_id' => 'required|integer|exists:mysql_external.carts',
            'inf_id' => 'required|integer|exists:mysql_external.raffle_info',
            'crf_prc_rff_id' => 'required|integer|exists:mysql_external.prices_raffles,prc_rff_id',
            'crf_tickets' => 'required|integer|min:1',
            'crf_renew' => 'integer|min:0|max:1',
            'crf_play_method' => 'required|integer|min:0|max:3'
        ];

        $this->validate($request, $rules);
        $validation = $this->validateCart($request->crt_id);
        if ($validation) return $validation;
        $lock = $this->check_for_cart_lock($request->crt_id);
        if ($lock) return $lock;

        $is_orca = ClientService::isOrca();

        if($is_orca){
            $raffle = Raffle::where('inf_id', '=', $request->inf_id)->where('inf_raffle_mx', '=', 0)->first();
        }else{
            $raffle = Raffle::where('inf_id', '=', $request->inf_id)->where('inf_raffle_mx', '=', 0)
                ->whereIn('inf_id', self::client_raffles(1)->pluck('product_id'))->first();
        }

        if (!$raffle) return $this->errorResponse(trans('lang.raffle_forbidden'), 403);
        $active_draw = $raffle->active_draw;
        if (!$active_draw) return $this->errorResponse(trans('lang.raffle_no_draw'), 403);


        $price_id = $request->crf_prc_rff_id;
        $prices = $raffle->raffle_prices->pluck('prc_rff_id');

        $raffle_price = $is_orca ? RafflePrice::where('prc_rff_id', $price_id)->first():
            RafflePrice::where('prc_rff_id', $price_id)->whereIn('prc_rff_id', $prices)->first();

        if (!$raffle_price) return $this->errorResponse(trans('lang.raffle_price_invalid'), 422);

        if ($raffle_price && $raffle_price->prc_rff_min_tickets > $request->crf_tickets) {
            return $this->errorResponse(trans('lang.raffle_min_tickets_invalid'), 422);
        }
        $prc_rff_draws = $raffle_price ? $raffle_price->prc_rff_draws : 0;
        $price = $raffle_price ? $raffle_price->price_line ? $raffle_price->price_line['price'] : 0 : 0;
        $cart_raffle = new CartRaffle();
        $cart_raffle->crt_id = $request->crt_id;
        $cart_raffle->inf_id = $request->inf_id;
        $cart_raffle->rff_id = $active_draw->rff_id;
        $cart_raffle->rtck_blocks = 1;
        $cart_raffle->crf_tickets = $request->crf_tickets * $prc_rff_draws;
        $cart_raffle->crf_ticket_byDraw = $request->crf_tickets;
        $cart_raffle->crf_ticket_nextDraw = $request->crf_tickets;
        $cart_raffle->crf_price = $request->crf_tickets * $price;
        $cart_raffle->crf_canceled = 0;
        $cart_raffle->crf_play_method = $request->crf_play_method;
        $cart_raffle->crf_printable_name= '';
        $cart_raffle->crf_prc_rff_id = $request->crf_prc_rff_id;
        $cart_raffle->crf_renew = $request->has("crf_renew") ? $request->crf_renew : 1;
        $cart_raffle->rsub_id = 0;
        $cart_raffle->bonus_id = 0;
        $cart_raffle->save();
        $cart = $cart_raffle->cart;
        $cart->crt_total += $cart_raffle->crf_price;
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
     *   path="/cart_raffles/{cart_raffle}",
     *   summary="Show Cart Raffle details ",
     *   tags={"Cart Raffles"},
     *   @SWG\Parameter(
     *     name="cart_raffle",
     *     in="path",
     *     description="Cart Raffle Id.",
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
    public function show(CartRaffle $cart_raffle) {
        $validation = $this->validateCart($cart_raffle->crt_id);
        if ($validation) return $validation;
        return $this->showOne($cart_raffle);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Core\Carts\Models\CartRaffle $cart_raffle
     * @return \Illuminate\Http\Response
     */
    /**
     * @SWG\Delete(
     *   path="/cart_raffles/{cart_raffle}",
     *   summary="Delete Cart Raffle",
     *   tags={"Cart Raffles"},
     *   @SWG\Parameter(
     *     name="cart_raffle",
     *     in="path",
     *     description="Cart Raffle Id.",
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
    public function destroy(CartRaffle $cart_raffle) {
        $validation = $this->validateCart($cart_raffle->crt_id);
        if ($validation) return $validation;
        $lock = $this->check_for_cart_lock($cart_raffle->crt_id);
        if ($lock) return $lock;
        $cart = $cart_raffle->cart;
        $cart->crt_total -= $cart_raffle->crf_price;
        $cart_raffle->delete();
        $this->cartAmounts($cart);
        $request = request();
        $request->merge(['pixel' => $cart->cart_step1()]);
        return $this->showOne($cart);
    }

    /**
     * @SWG\Get(
     *   path="/raffle_subscriptions/details/{raffle_subscription}",
     *   summary="Show user raffles subscriptions details",
     *   tags={"Subscriptions"},
     *   @SWG\Parameter(
     *     name="raffle_subscription",
     *     in="path",
     *     description="Raffle Subscription Id.",
     *     required=true,
     *     type="integer"
     *   ),
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/RaffleSubscriptionDetail")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */

    public function details(CartRaffle $cart_raffle) {
        $raffle_subscription = $cart_raffle->raffle_subscription;
        $raffle_draw  = $cart_raffle->raffle_draw;
        $raffle = $cart_raffle->raffleFromCartRaffles;
        $idRaffle = is_null($raffle) ? 0 : $raffle->inf_id;
        $result = [
            'order' => $cart_raffle->crt_id,
            'raffle_id' => $idRaffle,
            'order_date' => (string)$cart_raffle->cart->crt_date,
            'identifier' => $cart_raffle->crf_id,
            'draw_identifier' => $raffle_subscription ? $raffle_subscription->draw_id : null,
            'draw_extra_identifier' => $raffle_subscription ? $raffle_subscription->draw_extra_id : null,
            'name' => $raffle_subscription ? $raffle_subscription->raffle_name : null,
            'type_tag' => $raffle_subscription ? $raffle_subscription->raffle_type_tag : null,
            'prize' => $raffle_subscription ? $raffle_subscription->prizes : null,
            'currency' => $raffle_subscription ? $raffle_subscription->currency : null,
            'status' => $raffle_subscription ? $raffle_subscription->status : null,
            'status_tag' => $raffle_subscription ? $raffle_subscription->status_tag : null,
        ];
        $tickets_left = 0;
        $ticket_count = 0;
        $ticketList = [];
        $total = 0;
        if($raffle_subscription !== null){
            $ticketList = $raffle_subscription->tickets_list;
            $tickets_left = ($raffle_subscription->rsub_tickets + $raffle_subscription->rsub_ticket_extra - $raffle_subscription->rsub_emitted) < 0 ? 0 : ($raffle_subscription->rsub_tickets + $raffle_subscription->rsub_ticket_extra - $raffle_subscription->rsub_emitted);
            $ticket_count = $raffle_subscription->raffle_tickets->groupBy('rff_id')->count();
            $total = $ticket_count + ceil($tickets_left / $raffle_subscription->rsub_ticket_byDraw);
        }
        if ($raffle_draw && $raffle_draw->rff_status == 1) {
            $emitted = $ticket_count -1;
        } else {
            $emitted = $ticket_count;
        }
        $result['draws_emitted'] = $emitted;
        $result['draws_total'] = $total;
        $result['tickets'] = $cart_raffle->tickets()['tickets'];
        $result['tickets_tag'] = $cart_raffle->tickets()['tickets_tag'];
        $result['tickets_list'] = $ticketList;
        return $this->successResponse(['data' => $result]);
    }
}
