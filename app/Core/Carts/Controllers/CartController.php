<?php

namespace App\Core\Carts\Controllers;

use App\Core\Carts\Models\Cart;
use App\Core\Clients\Models\Client;
use App\Core\Rapi\Services\Log;
use App\Core\Rapi\Models\Price;
use App\Core\Raffles\Models\RafflePrice;
use App\Core\ScratchCards\Models\ScratchCardPrice;
use App\Core\Base\Services\ClientService;
use App\Core\Base\Services\FastTrackLogService;
use App\Core\Telem\Services\TelemCartService;
use App\Core\Syndicates\Models\SyndicatePrice;
use App\Core\Syndicates\Models\SyndicateRafflePrice;
use App\Core\Base\Traits\CartUtils;
use App\Core\Carts\Transforms\CartTransformer;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;

class CartController extends ApiController
{
    use CartUtils;

    public function __construct() {
        parent::__construct();
        $this->middleware('client.credentials');
        $this->middleware('auth:api')->only(['update']);
        $this->middleware('transform.input:' . CartTransformer::class)->only(['store', 'update', 'apply_promocode']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    /**
     * @SWG\Post(
     *   path="/carts",
     *   summary="Create cart",
     *   tags={"Carts"},
     *   consumes={"multipart/form-data"},
     *   @SWG\Parameter(
     *     name="track",
     *     in="formData",
     *     description="Cart track",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="affiliate_cookie",
     *     in="formData",
     *     description="Cart affiliate cookie",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="utm_source",
     *     in="formData",
     *     description="Cart utm source",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="utm_campaign",
     *     in="formData",
     *     description="Cart utm campaign",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="utm_medium",
     *     in="formData",
     *     description="Cart utm medium",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="utm_content",
     *     in="formData",
     *     description="Cart utm content",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="utm_term",
     *     in="formData",
     *     description="Cart utm term",
     *     required=false,
     *     type="string"
     *   ),
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *
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
    public function store(Request $request) {
        $is_orca = ClientService::isOrca();

        if($is_orca){
            $request->validate([
                "agent_id" => "required",
                "user_id" => "required"
            ]);
        }

        $user_id = request('user_id') ? request('user_id') : 0;
        $site = Client::where('id', $request['oauth_client_id'])->first()->site;
        $cart = new Cart();
        $cart->usr_id = $user_id;
        $cart->crt_price = 0;
        $cart->cart_type = 1;
        $cart->crt_currency = $request->country_currency;
        $cart->crt_lastStep = 0;
        $cart->crt_done = 0;
        $cart->crt_status = 0;
        $cart->cart_type = 1;
        $cart->crt_total = 0;
        $cart->crt_discount = 0;
        $cart->crt_from_account = 0;
        $cart->site_id = $is_orca ? $request['client_site_id'] : $site->site_id;
        $cart->crt_ip = $request['user_ip'];
        $cart->crt_host = $is_orca ? "admin telem" : str_replace('http://', '', $site->site_url);
        $cart->crt_affcookie = $request->crt_affcookie ? $request->crt_affcookie : '';
        $cart->crt_track = $request->crt_track ? $request->crt_track : '';
        $cart->utm_source = $request->utm_source ? $request->utm_source : '';
        $cart->utm_campaign = $request->utm_campaign ? $request->utm_campaign : '';
        $cart->utm_medium = $request->utm_medium ? $request->utm_medium : '';
        $cart->utm_content = $request->utm_content ? $request->utm_content : '';
        $cart->utm_term = $request->utm_term ? $request->utm_term : '';
        $cart->save();

        if($is_orca){
            $request->merge(["crt_id" => $cart->crt_id]);
            $telem = TelemCartService::getInstance();
            if(!$telem->telemCart($request)){
                return $this->errorResponse($telem->getError(), 500);
            }
        }


        return $this->showOne($cart, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param Cart $cart
     * @return \Illuminate\Http\Response
     */
    /**
     * @SWG\Get(
     *   path="/carts/{cart}",
     *   summary="Show cart details ",
     *   tags={"Carts"},
     *   @SWG\Parameter(
     *     name="cart",
     *     in="path",
     *     description="Cart Id.",
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
    public function show(Cart $cart) {
//          validate cart from user and valid in site
        $validation = $this->validateCart($cart->crt_id);
        if ($validation) return $validation;
        $request = request();
        $request->merge(['pixel' => $cart->cart_step1()]);
        return $this->showOne($cart);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request    $request
     * @param  \App\Core\Carts\Models\Cart $cart
     * @return \Illuminate\Http\Response
     */
    /**
     * @SWG\Put(
     *   path="/carts/{cart}",
     *   summary="Update cart (only set user)",
     *   tags={"Carts"},
     *   consumes={"application/x-www-form-urlencoded"},
     *   @SWG\Parameter(
     *     name="cart",
     *     in="path",
     *     description="Cart Id.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="use_vip_points",
     *     in="formData",
     *     description="Use VIP points",
     *     required=false,
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
     *       @SWG\Property(property="data", allOf={ @SWG\Schema(ref="#/definitions/Cart"), }),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function update(Request $request, Cart $cart) {
        $rules = [
            'use_vip_points' => 'integer|min:0|max:1'
        ];
        $this->validate($request, $rules);
        $cart_sys_id = $cart->site ? $cart->site->sys_id : null;
        $user = request()->user();
        if(!$cart_sys_id || $request->client_sys_id != $cart_sys_id || $cart->crt_status != 0 || $cart->crt_lastStep > 2 || ($cart->usr_id != 0 && $cart->usr_id != $user->usr_id))
            return $this->errorResponse(trans('lang.cart_forbidden'), 422);
        if ($cart->crt_currency != $request['country_currency'])
            return $this->errorResponse(trans('lang.cart_different_currency'), 422);

        if ($request->vip_points == 1) {
            $user_points = $user->usr_points;
            if ($user_points > 0) {

                if ($cart->crt_promotion_code != '') {
                    $cart->reset_promocode();
                }
                $user_point_cash = $user->user_point_cash;
                $crt_total = $cart->crt_total;
                $crt_price = round($crt_total - $user_point_cash, 2);
                if($user_point_cash >= $crt_total) {
                    $crt_price = 0;
                    $crt_discount = $crt_total;
                    $crt_promotion_points = round(($crt_total * $user_points)/$user_point_cash,2);
                }else{
                    $crt_discount = $user_point_cash;
                    $crt_promotion_points = $user_points;
                }
                $cart->crt_price = $crt_price;
                $cart->crt_discount = $crt_discount;
                $cart->crt_promotion_points = $crt_promotion_points;
            } else {
                // no tiene vip points
            }

        } elseif($request->vip_points == 0) {
            if ($cart->crt_promotion_code != '') {
                $promotion_code = $cart->crt_promotion_code;
                $cart->reset_promocode();
                $cart->apply_promo($promotion_code);
            }

        }
        $products = $this->validate_by_country($cart->crt_id);
        $this->cartAmounts($cart);
        $cart->usr_id = $cart->usr_id == 0 ? request()->user()->usr_id : $cart->usr_id;
        $cart->save();
        $cart->total_products = $products['total_products'];
        $cart->deleted_products = $products['deleted_products'];
        $request->merge(['pixel' => $cart->cart_step1()]);
        return $this->showOne($cart, 200);
    }


    /**
     * @param Request $request
     * @param Cart $cart
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @SWG\Post(
     *   path="/carts/apply_promocode/{cart}",
     *   summary="Apply promocode",
     *   tags={"Carts"},
     *   @SWG\Parameter(
     *     name="cart",
     *     in="path",
     *     description="Cart Id.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="promo_code",
     *     in="formData",
     *     description="Promocode.",
     *     required=true,
     *     type="string"
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
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function apply_promocode(Request $request, Cart $cart) {
//          validate cart from user and valid in site
        $validation = $this->validateCart($cart->crt_id);
        if ($validation) return $validation;
        $rules = [
            'promo_code' => 'required',
        ];
        $this->validate($request, $rules);
        $cart->reset_promocode();
        if ($cart->apply_promo($request->promo_code)) {
            $cart->save();
        } else {
            return $this->errorResponse(trans('lang.invalid_promocode'), 422);
        }
        $request->merge(['pixel' => $cart->cart_step1()]);
        return $this->showOne($cart);
    }


    /**
     * @SWG\Post(
     *   path="/product_by_price",
     *   summary="Get product id by price",
     *   tags={"Carts"},
     *   @SWG\Parameter(
     *     name="product_type",
     *     in="formData",
     *     description="Product type.",
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
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(property="data", description="Product Id", type="array",
     *         @SWG\Items(
     *           @SWG\Property(
     *             property="product_id",
     *             description="Product Id",
     *             type="integer",
     *             example=1,
     *           ),
     *         )
     *       ),
     *       @SWG\Property(property="code", description="Status Code", example="200"),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */

    public function product_by_price(Request $request) {
        $rules = [
            'product_type' => 'required|integer',
            'price_id' => 'required|integer'
        ];
        $this->validate($request, $rules);

        switch ($request->product_type) {
            case 1:
                $price = Price::where('prc_id', '=', $request->price_id)
                    ->where('active', '=', 1)
                    ->where('sys_id', '=', $request['client_sys_id'])
                    ->first();
                return $price ? $this->successResponse(['data' => ['product_id' => $price->lot_id]]) : $this->errorResponse(trans('lang.lottery_price_invalid'), 422);
                break;
            case 2:
                $price = SyndicatePrice::where('prc_id', '=', $request->price_id)
                    ->where('active', '=', 1)
                    ->where('sys_id', '=', $request['client_sys_id'])
                    ->first();
                return $price ? $this->successResponse(['data' => ['product_id' => $price->syndicate_id]]) : $this->errorResponse(trans('lang.syndicate_price_invalid'), 422);
                break;
            case 3:
                $price = SyndicateRafflePrice::where('prc_id', '=', $request->price_id)
                    ->where('active', '=', 1)
                    ->where('sys_id', '=', $request['client_sys_id'])
                    ->first();
                return $price ? $this->successResponse(['data' => ['product_id' => $price->rsyndicate_id]]) : $this->errorResponse(trans('lang.raffle_syndicate_price_invalid'), 422);
                break;
            case 4:
                $price = RafflePrice::where('prc_rff_id', '=', $request->price_id)
                    ->where('active', '=', 1)
                    ->where('sys_id', '=', $request['client_sys_id'])
                    ->first();
                return $price ? $this->successResponse(['data' => ['product_id' => $price->inf_id]]) : $this->errorResponse(trans('lang.raffle_price_invalid'), 422);
                break;
            case 7:
                $price = ScratchCardPrice::where('prc_id', '=', $request->price_id)
                    ->where('active', '=', 1)
                    ->where('sys_id', '=', $request['client_sys_id'])
                    ->first();
                return $price ? $this->successResponse(['data' => ['product_id' => $price->scratches_id]]) : $this->errorResponse(trans('lang.scratch_price_invalid'), 422);
                break;
            default:
                return $this->errorResponse(trans('lang.invalid_product_type'), 422);


        }
    }
}
