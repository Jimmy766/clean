<?php


namespace App\Core\Carts\Controllers;


use App\Core\Carts\Models\Cart;
use App\Core\Carts\Requests\CartSyndicateWheelDeleteRequest;
use App\Core\Carts\Requests\CartSyndicateWheelCreateRequest;
use App\Core\Carts\Requests\CartSyndicateWheelEditRequest;
use App\Core\Syndicates\Models\Syndicate;
use App\Core\Syndicates\Models\SyndicateCartSubscription;
use App\Core\Syndicates\Models\SyndicatePrice;
use App\Core\Base\Traits\CartUtils;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Swagger\Annotations as SWG;

class CartSyndicateWheelController extends ApiController
{

    use CartUtils;

    public function __construct() {
        parent::__construct();
        $this->middleware('client.credentials')->except('index', 'details');
        $this->middleware('auth:api')->only('index', 'details');
    }

    /**
     * @SWG\Get(
     *   path="/cart_syndicate_wheels",
     *   summary="list Cart syndicate wheels",
     *   tags={"Cart Wheels"},
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
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
    public function index(){
        $syndicates = Syndicate::query()->with([
            'syndicate_prices.syndicate_price_lines',
            'syndicate_prices.lottery_time_draws',
            'syndicate_lotteries.lottery.draws',
            'syndicate_lotteries.draws',
            'syndicate_prices.syndicate.syndicate_lotteries.lottery'
        ])
            ->where('active', '=', 1)
            ->where('has_wheel', '=', 1)
            ->whereIn('id', self::client_syndicates(1)->pluck('product_id'))
            ->getFromCache();
        return $this->showAllNoPaginated($syndicates);
    }

    /**
     * @SWG\Post(
     *   path="/cart_syndicate_wheels",
     *   summary="Create Cart syndicate wheels",
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
     *     name="syndicate_id",
     *     in="formData",
     *     description="Syndicate syndicate_id",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="syndicate_prc_id",
     *     in="formData",
     *     description="Syndicate syndicate_prc_id",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="ticket_byDraw",
     *     in="formData",
     *     description="ticket_byDraw",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="syndicate_picks_id",
     *     in="formData",
     *     description="Syndicate picks",
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
    public function store(CartSyndicateWheelCreateRequest $request)
    {
        $validation = $this->validateCart($request->crt_id);
        if ($validation)
            return $validation;
        $lock = $this->check_for_cart_lock($request->crt_id);
        if ($lock)
            return $lock;

        $syndicate_price = SyndicatePrice::where('prc_id', $request->syndicate_prc_id)->first();

        if(!$syndicate_price){
            return $this->errorResponse(trans('lang.syndicate_price_invalid'), 422);
        }

        $syndicate_cart_subscription = new SyndicateCartSubscription();
        $syndicate_cart_subscription->crt_id = $request->crt_id;
        $syndicate_cart_subscription->syndicate_id = $request->syndicate_id;
        $syndicate_cart_subscription->cts_renew = 1;
        $syndicate_cart_subscription->cts_price = $request->ticket_byDraw *
            $syndicate_price->syndicate_price_line['price'];
        $syndicate_cart_subscription->cts_ticket_extra = $request->has("ticket_extra") ?
            $request->get("ticket_extra") : 0;
        $syndicate_cart_subscription->cts_ticket_byDraw = $request->ticket_byDraw;
        $syndicate_cart_subscription->cts_syndicate_prc_id = $request->syndicate_prc_id;
        $syndicate_cart_subscription->syndicate_picks_id = $request->syndicate_picks_id;
        $syndicate_cart_subscription->cts_ticket_nextDraw = 1;
        $syndicate_cart_subscription->save();

        $cart = $syndicate_cart_subscription->cart;
        $cart->crt_total += $syndicate_cart_subscription->cts_price;
        $this->cartAmounts($cart);
        $request->merge(['pixel' => $cart->cart_step1()]);
        return $this->showOne($cart, 201);
    }

    /**
     * @SWG\Put(
     *   path="/cart_syndicate_wheels/{cart_syndicate_wheel}",
     *   summary="Update Cart Syndicate details ",
     *   tags={"Cart Wheels"},
     *   @SWG\Parameter(
     *     name="cart_syndicate_wheels",
     *     in="path",
     *     description="Cart syndicate Wheel Id.",
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
     *     name="syndicate_prc_id",
     *     in="formData",
     *     description="Syndicate syndicate_prc_id",
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
    public function update($id, CartSyndicateWheelEditRequest $request){
        try{

            $validation = $this->validateCart($request->crt_id);

            if ($validation)
                return $validation;
            $lock = $this->check_for_cart_lock($request->crt_id);

            if ($lock)
                return $lock;
            //if ($syndicate_cart_subscription->bonus_id != 0) return $this->errorResponse(trans('lang.cart_syndicate_update'), 403);

            $syndicate_cart_subscription = SyndicateCartSubscription::findOrFail($id);


            $syndicate_price = SyndicatePrice::where('prc_id', $request->syndicate_prc_id)->first();

            if (!$syndicate_price)
                return $this->errorResponse(trans('lang.syndicate_price_invalid'), 422);

            $tickets_by_draw = $request->ticket_byDraw ?
                $request->ticket_byDraw :
                $syndicate_cart_subscription->cts_ticket_byDraw;

            $cart = $syndicate_cart_subscription->cart;
            $cart->crt_total -= $syndicate_cart_subscription->cts_price;
            $syndicate_cart_subscription->cts_price = $tickets_by_draw * $syndicate_price->syndicate_price_line['price'];
            $syndicate_cart_subscription->cts_ticket_byDraw = $tickets_by_draw;
            $syndicate_cart_subscription->cts_ticket_nextDraw = $tickets_by_draw;
            $syndicate_cart_subscription->cts_syndicate_prc_id = $syndicate_price->prc_id;
            if($request->has('syndicate_picks_id'))
                $syndicate_cart_subscription->syndicate_picks_id = $request->syndicate_picks_id;
            $syndicate_cart_subscription->save();
            $cart->crt_total += $syndicate_cart_subscription->cts_price;
            $this->cartAmounts($cart);
            $request->merge(['pixel' => $cart->cart_step1()]);

            return $this->showOne($cart);

        }  catch (\Exception $ex){
            return $this->errorResponse(trans('lang.cart_valid'), 422);
        }
    }


    public function show(Request $request) {
    }

    /**
     * @SWG\Delete(
     *   path="/cart_syndicate_wheels/{cart_syndicate_wheel}",
     *   summary="delete Cart Syndicate details ",
     *   tags={"Cart Wheels"},
     *   @SWG\Parameter(
     *     name="cart_syndicate_wheels",
     *     in="path",
     *     description="Cart syndicate Wheel Id.",
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
    public function destroy($cts_id, CartSyndicateWheelDeleteRequest $request) {

        try{
            $validation = $this->validateCart($request->crt_id);
            if ($validation) return $validation;

            $lock = $this->check_for_cart_lock($request->crt_id);
            if ($lock) return $lock;

            $cart_subscription_wheel = SyndicateCartSubscription::where("cts_id", "=", $cts_id)
                ->where("crt_id", "=", $request->crt_id)->first();
            $cart = Cart::findOrFail($request->crt_id);
            $cart->crt_total -= $cart_subscription_wheel->cts_price;
            $cart_subscription_wheel->delete();
            $this->cartAmounts($cart);
            $request = request();
            $request->merge(['pixel' => $cart->cart_step1()]);
            return $this->showOne($cart);

        }catch (\Exception $ex){
            return $this->errorResponse(trans('lang.cart_syndicate_invalid'), 422);
        }
    }
}
