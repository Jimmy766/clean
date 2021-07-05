<?php

namespace App\Core\Carts\Controllers;

use App\Core\Carts\Models\Cart;
use App\Core\Carts\Models\CartSubscription;
use App\Core\Carts\Models\CartSubscriptionPick;
use App\Core\Base\Classes\ModelConst;
use App\Core\Rapi\Requests\Rules\CheckInsureActiveAndModifierRule;
use App\Core\Lotteries\Models\Lottery;
use App\Core\Lotteries\Models\LotteryFirstDayToPlay;
use App\Core\Rapi\Models\Price;
use App\Core\Base\Services\ClientService;
use App\Core\Base\Services\FastTrackLogService;
use App\Core\Lotteries\Services\GenerateQuickPicksService;
use App\Core\Lotteries\Services\LotteryService;
use App\Core\Base\Services\SendLogConsoleService;
use App\Core\Base\Traits\CartUtils;
use App\Core\Base\Traits\PicksValidation;
use App\Core\Carts\Transforms\CartSubscriptionTransformer;
use App\Http\Controllers\ApiController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class CartLotterySubscriptionController extends ApiController
{
    use CartUtils;
    use PicksValidation;

    /**
     * @var SendLogConsoleService
     */
    private $sendLogConsoleService;
    /**
     * @var GenerateQuickPicksService
     */
    private $generateQuickPicksService;

    /**
     * CartSubscriptionController constructor.
     * @param SendLogConsoleService     $sendLogConsoleService
     * @param GenerateQuickPicksService $generateQuickPicksService
     */
    public function __construct(
        SendLogConsoleService $sendLogConsoleService,
        GenerateQuickPicksService $generateQuickPicksService
    ) {
        parent::__construct();
        $this->middleware('client.credentials');
        $this->middleware('transform.input:' . CartSubscriptionTransformer::class)
            ->only([ 'store', 'update' ]);
        $this->sendLogConsoleService     = $sendLogConsoleService;
        $this->generateQuickPicksService = $generateQuickPicksService;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    /**
     * @SWG\Post(
     *   path="/cart_lotteries",
     *   summary="Create Cart Lottery",
     *   tags={"Cart Lotteries"},
     *   consumes={"multipart/form-data"},
     *   @SWG\Parameter(
     *     name="cart_id",
     *     in="formData",
     *     description="Cart Id.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="lottery_id",
     *     in="formData",
     *     description="Lottery Id.",
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
     *     name="pick_type",
     *     in="formData",
     *     description="Pick Type. (1 => Quick pick, 3 => User pick)",
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
     *
     *   @SWG\Parameter(
     *     name="pick_balls",
     *     in="formData",
     *     description="Pick Balls.",
     *     required=false,
     *     type="array",
     *     @SWG\Items(type="string")
     *   ),
     *   @SWG\Parameter(
     *     name="pick_extra_balls",
     *     in="formData",
     *     description="Pick Extra Balls.",
     *     required=false,
     *     type="array",
     *     @SWG\Items(type="string")
     *   ),
     * @SWG\Parameter(
     *     name="cts_day_to_play",
     *     in="formData",
     *     description="For La Primitiva (thursday = 4, saturday = 6 and both = 7)",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="draws",
     *     in="formData",
     *     description="Draw quantity for individual tickets.",
     *     required=false,
     *     type="integer",
     *   ),
     *   @SWG\Parameter(
     *     name="first_date_to_play",
     *     in="formData",
     *     description="First day to play",
     *     type="string",
     *     format="date",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="price_modifier_1",
     *     in="formData",
     *     description="price_modifier_1",
     *     required=false,
     *     type="integer",
     *   ),
     *   @SWG\Parameter(
     *     name="price_modifier_2",
     *     in="formData",
     *     description="price_modifier_2",
     *     required=false,
     *     type="integer",
     *   ),
     *   @SWG\Parameter(
     *     name="price_modifier_3",
     *     in="formData",
     *     description="price_modifier_3",
     *     required=false,
     *     type="integer",
     *   ),
     *   @SWG\Parameter(
     *     name="type_insure_modifier",
     *     in="formData",
     *     description="type_insure_modifier",
     *     required=false,
     *     type="integer",
     *   ),
     *   @SWG\Parameter(
     *     name="boosted_modifier",
     *     in="formData",
     *     description="boosted_modifier",
     *     required=false,
     *     type="integer",
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
            'crt_id'            => 'required|integer|exists:mysql_external.carts',
            'lot_id'            => 'required|integer|exists:mysql_external.lotteries',
            'prc_id'            => 'required|integer|exists:mysql_external.prices,prc_id',
            'price_modifier_1'  => [
                'nullable',
                'integer',
                'min:0',
                'max:1',
            ],
            'price_modifier_2'  => [
                'nullable',
                'integer',
                'min:0',
                'max:1',
            ],
            'price_modifier_3'  => [
                'nullable',
                'integer',
                'min:0',
                'max:1',
            ],
            'boosted_modifier'  => [
                'nullable',
                'integer',
                new
                CheckInsureActiveAndModifierRule(),
            ],
            'cts_pck_type'      => 'required|integer|in:1,3',
            'pick_balls'        => 'required_if:cts_pck_type,3|array',
            'cts_ticket_byDraw' => 'required|integer|min:1',
        ];

        $is_orca = ClientService::isOrca();

        $this->validate($request, $rules);
        $validation = $this->validateCart($request->crt_id);
        if ($validation) return $validation;
        $lock = $this->check_for_cart_lock($request->crt_id);
        if ($lock) return $lock;

        $lottery = Lottery::where('lot_id', '=', $request->lot_id)->where('lot_active', '=',1)
            ->whereIn('lot_id', self::client_lotteries(1)->pluck('product_id'))
            ->first();
        if (!$lottery) return $this->errorResponse(trans('lang.lottery_forbidden'), 403);
        $prices = $lottery->prices_list->pluck('prc_id');

        $price = $is_orca ? Price::where('prc_id', $request->prc_id)->first()
            : Price::where('prc_id', $request->prc_id)->whereIn('prc_id', $prices)->first();


        if (!$price) return $this->errorResponse(trans('lang.lottery_price_invalid'), 422);

        $tickets_price = $price->price_line['price'];

        if($request->price_modifier_1 == 1 ){
            $tickets_price += $price->price_line[ 'modifier1' ];
        }
        if($request->price_modifier_2 == 1 ){
            $tickets_price += $price->price_line[ 'modifier2' ];
        }
        if($request->price_modifier_3 == 1 ){
            $tickets_price += $price->price_line[ 'modifier3' ];
        }
        if ($request->boosted_modifier !== null && $request->boosted_modifier !== 0) {
            $pricesLines = $price->prices_lines_attributes;
            $pricesLines = collect($pricesLines);
            $pricesLines = $pricesLines->where('modifier_id', $request->boosted_modifier);
            if ($pricesLines->count() > 0) {
                $priceLine     = $pricesLines->first();
                $tickets_price += $priceLine['modifier_price'];
            }
        }
        if ($price->prc_model_type == 1) {
            $max_draws = 4 * $lottery->days_to_play();
            $rules = [
                'draws' => 'required|integer|min:1|max:'.$max_draws,
            ];
            $this->validate($request, $rules);
            if ($request->lot_id == Lottery::$LA_PRIMITIVA && $request->cts_day_to_play == 7) {
                $request->draws *= 2;
            }
            $tickets = $price->prc_draws * $request->draws;
            $tickets_price = $tickets_price * $request->draws;
        } else {
            $tickets = $price->prc_draws;
        }


        [ $request, $cart_subscription_picks, $error ] = $this->generateQuickPicksService->execute(
            $request,
            $lottery
        );

        if($error !== null){
            return $error;
        }

        if($request->cts_pck_type == 3) {
            $cart_subscription_picks = [];

            $validation = $this->validatePicks($request, $lottery, $cart_subscription_picks);
            if ($validation)
                return $validation;
        }

        /**
         * Para Cash4Life
         */
        if($request->lot_id == Lottery::$CASH4LIFE_ID){
            if(!$request->has("first_date_to_play") || !$request->first_date_to_play){
                $request->first_date_to_play = Carbon::tomorrow()->format("Y-m-d");
            }
            $cts_dtp = ($request->draws != 1) ? 7 :
                Carbon::createFromFormat("Y-m-d", $request->first_date_to_play)->dayOfWeek;

            $request->merge(["cts_day_to_play" => $cts_dtp]);
        }

        $ctsDayToPlayDefault = 7;
        if($request->lot_id == Lottery::$CASH4LIFE_ID || $request->lot_id == Lottery::$LA_PRIMITIVA){
            $ctsDayToPlayDefault = $lottery->days_to_play();
        }

        $cart_subscription = new CartSubscription();
        $cart_subscription->crt_id = $request->crt_id;
        $cart_subscription->lot_id = $request->lot_id;
        $cart_subscription->cts_subExtension = $price->prc_time;
        $cart_subscription->cts_tickets = $tickets * $request->cts_ticket_byDraw;
        $cart_subscription->cts_price = $tickets_price * $request->cts_ticket_byDraw;
        $cart_subscription->cts_ticket_extra = 0;
        $cart_subscription->cts_pck_type = $request->cts_pck_type == 3 ? 3 : 2;
        $cart_subscription->cts_ticket_byDraw = $request->cts_ticket_byDraw;
        $cart_subscription->cts_draws = 0;
        $cart_subscription->cts_ticket_nextDraw = $request->has("cts_paused") && $request->cts_paused ? 0 : $request->cts_ticket_byDraw;
        $cart_subscription->cts_winning_behaviour = 1;
        /* Si no es renovable, no es renovable, sino me fijo si viene el campo (Mainly orca) o lo dejo renovable*/
        $cart_subscription->cts_renew = ($price->prc_draws == 1 || $price->prc_draws == 2 || $price->prc_model_type == 1) ? 1 : ($request->has("cts_renew") ? $request->cts_renew : 0);
        $cart_subscription->cts_day_to_play = $request->has("cts_day_to_play") ? $request->get("cts_day_to_play") : $ctsDayToPlayDefault;
        $cart_subscription->cts_prc_id = $price->prc_id;
        $cart_subscription->cts_wheel = 0;
        $cart_subscription->wheel_id = 0;
        //$cart_subscription->cts_next_draw_id = $lottery->getActiveDraw() ? $lottery->getActiveDraw()->draw_id : 0;
        $cart_subscription->cts_next_draw_id = 0;
        $cart_subscription->bonus_id = 0;
        $cart_subscription->cts_modifier_1 = $request->price_modifier_1;
        $cart_subscription->cts_modifier_2 = $request->price_modifier_2;
        $cart_subscription->cts_modifier_3 = $request->price_modifier_3;
        $cart_subscription->boosted_modifier_id = $request->boosted_modifier === null ? 0 : $request->boosted_modifier;
        $cart_subscription->save();

        if($request->cts_pck_type == 1 && $lottery->lot_pick_type == 1){
            $request->cts_pck_type = 2;
        }

        if($request->cts_pck_type == 3 || ($request->cts_pck_type == 2 && $request->lot_id != 1000)) {
            foreach ($cart_subscription_picks as $cart_subscription_pick) {
                $cart_subscription_pick->cts_id = $cart_subscription->cts_id;
                $cart_subscription_pick->save();
            }
        }

        /*
         * Cash4Life
         */
        if($request->lot_id == Lottery::$CASH4LIFE_ID){
            LotteryFirstDayToPlay::create([
                "cts_id" => $cart_subscription->cts_id,
                "first_datetoplay" => $request->first_date_to_play
            ]);
        }

        $cart = $cart_subscription->cart;
        $cart->crt_total += $cart_subscription->cts_price;
        $this->cartAmounts($cart);
        $request->merge(['pixel' => $cart->cart_step1()]);
        $dataLog['cart_subscription'] = $cart_subscription->toArray();
        $dataLog['cart'] = $cart_subscription->cart->toArray();
        $this->sendLogConsoleService->execute(
            $request,
            'cart-subscription',
            'access',
            '',
            $dataLog
        );
        return $this->showOne($cart, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Core\Carts\Models\CartSubscription $cart_subscription
     * @return \Illuminate\Http\Response
     */
    /**
     * @SWG\Get(
     *   path="/cart_lotteries/{cart_lottery}",
     *   summary="Show Cart Lottery details ",
     *   tags={"Cart Lotteries"},
     *   @SWG\Parameter(
     *     name="cart_lottery",
     *     in="path",
     *     description="Cart Lottery Id.",
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
     *       @SWG\Property(property="data", allOf={ @SWG\Schema(ref="#/definitions/CartSubscription"), }),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     * @param \App\Core\Carts\Models\CartSubscription $cart_subscription
     * @return bool|\Illuminate\Http\JsonResponse
     */
    public function show(CartSubscription $cart_subscription) {
        $validation = $this->validateCart($cart_subscription->crt_id);
        if ($validation) return $validation;
        return $this->showOne($cart_subscription);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request                $request
     * @param  \App\Core\Carts\Models\CartSubscription $cartSuscription
     * @return \Illuminate\Http\Response
     */
    /**
     * @SWG\Put(
     *   path="/cart_lotteries/{cart_lottery}",
     *   summary="Update Cart Lottery ",
     *   tags={"Cart Lotteries"},
     *   consumes={"application/x-www-form-urlencoded"},
     *   @SWG\Parameter(
     *     name="cart_lottery",
     *     in="path",
     *     description="Cart Lottery Id.",
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
     *     name="pick_type",
     *     in="formData",
     *     description="Pick Type Id.",
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
     *   @SWG\Parameter(
     *     name="pick_balls",
     *     in="formData",
     *     description="Pick Balls.",
     *     required=false,
     *     type="array",
     *     @SWG\Items(type="string")
     *   ),
     *   @SWG\Parameter(
     *     name="pick_extra_balls",
     *     in="formData",
     *     description="Pick Extra Balls.",
     *     required=false,
     *     type="array",
     *     @SWG\Items(type="string")
     *   ),
     *   @SWG\Parameter(
     *     name="draws",
     *     in="formData",
     *     description="Draw quantity for individual tickets.",
     *     required=false,
     *     type="integer",
     *   ),
     *
     *   @SWG\Parameter(
     *     name="price_modifier_1",
     *     in="formData",
     *     description="price_modifier_1",
     *     required=false,
     *     type="integer",
     *   ),
     *   @SWG\Parameter(
     *     name="price_modifier_2",
     *     in="formData",
     *     description="price_modifier_2",
     *     required=false,
     *     type="integer",
     *   ),
     *   @SWG\Parameter(
     *     name="price_modifier_3",
     *     in="formData",
     *     description="price_modifier_3",
     *     required=false,
     *     type="integer",
     *   ),
     *
     *   @SWG\Parameter(
     *     name="first_date_to_play",
     *     in="formData",
     *     description="First day to play",
     *     type="string",
     *     format="date",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="type_insure_modifier",
     *     in="formData",
     *     description="type_insure_modifier",
     *     required=false,
     *     type="integer",
     *   ),
     *   @SWG\Parameter(
     *     name="boosted_modifier",
     *     in="formData",
     *     description="boosted_modifier",
     *     required=false,
     *     type="integer",
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
     * @param Request                                 $request
     * @param \App\Core\Carts\Models\CartSubscription $cart_subscription
     * @return bool|\Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, CartSubscription $cart_subscription) {
        $dataLog['cart_subscription'] = $cart_subscription->toArray();
        $dataLog['cart'] = $cart_subscription->cart->toArray();
        $this->sendLogConsoleService->execute(
            $request,
            'cart-subscription-before-operation',
            'access',
            '',
            $dataLog
        );
        $rules = [
            'prc_id' => 'integer|exists:mysql_external.prices,prc_id',
            'cts_pck_type' => 'integer|in:1,3',
            'pick_balls' => 'required_if:cts_pck_type,3|array',
            'cts_ticket_byDraw' => 'integer|min:1',
            'price_modifier_1'  => [
                'nullable',
                'integer',
                'min:0',
                'max:1',
            ],
            'price_modifier_2'  => [
                'nullable',
                'integer',
                'min:0',
                'max:1',
            ],
            'price_modifier_3'  => [
                'nullable',
                'integer',
                'min:0',
                'max:1',
            ],
            'boosted_modifier'  => [
                'nullable',
                'integer',
                new
                CheckInsureActiveAndModifierRule(),
            ],
        ];
        $this->validate($request, $rules);
        $cart_subscription->cts_modifier_1 = $request->price_modifier_1 ?? $cart_subscription->cts_modifier_1;
        $cart_subscription->cts_modifier_2 = $request->price_modifier_2 ?? $cart_subscription->cts_modifier_2;
        $cart_subscription->cts_modifier_3 = $request->price_modifier_3 ?? $cart_subscription->cts_modifier_3;
        $cart_subscription->boosted_modifier_id = $request->boosted_modifier === null ? $cart_subscription->boosted_modifier_id : $request->boosted_modifier;

        $this->validateOnlyModifier($cart_subscription);

        $is_orca = ClientService::isOrca();
        $validation = $this->validateCart($cart_subscription->crt_id);
        if ($validation) return $validation;
        $lottery = $cart_subscription->lottery;
        if ($request->cts_pck_type) {
            $cart_subscription->cts_pck_type = $request->cts_pck_type == 3 ? 3 : 2;

            [ $request, $cart_subscription_picks, $error ] = $this->generateQuickPicksService->execute(
                $request,
                $lottery
            );

            if($error !== null){
               return $error;
            }

            if($request->cts_pck_type == 3) {
                $cart_subscription_picks = [];
                $validation = $this->validatePicks($request, $lottery, $cart_subscription_picks);
                if ($validation)
                    return $validation;
            }


            $cart_subscription->cart_subscription_picks->each(function (CartSubscriptionPick $item) {
                $item->delete();
            });
        }
        if ($request->prc_id) {
            $lock = $this->check_for_cart_lock($cart_subscription->crt_id);
            if ($lock) return $lock;
            $cart = $cart_subscription->cart;
            $cart->crt_total -= $cart_subscription->cts_price;
            $prices = $lottery->prices_list->pluck('prc_id');

            $price = $is_orca ? Price::where('prc_id', $request->prc_id)->first()
                : Price::where('prc_id', $request->prc_id)->whereIn('prc_id', $prices)->first();

            if (!$price) return $this->errorResponse(trans('lang.lottery_price_invalid'), 422);

            $tickets_price = $price->price_line['price'];

            if($cart_subscription->cts_modifier_1 == 1 ){
                $tickets_price += $price->price_line[ 'modifier1' ];
            }
            if($cart_subscription->cts_modifier_2 == 1 ){
                $tickets_price += $price->price_line[ 'modifier2' ];
            }
            if($cart_subscription->cts_modifier_2 == 1 ){
                $tickets_price += $price->price_line[ 'modifier3' ];
            }

            if ($cart_subscription->boosted_modifier_id !== null && $cart_subscription->boosted_modifier_id != 0) {
                $pricesLines = $price->prices_lines_attributes;
                $pricesLines = collect($pricesLines);
                $pricesLines = $pricesLines->where('modifier_id', $cart_subscription->boosted_modifier_id);
                if ($pricesLines->count() > 0) {
                    $priceLine     = $pricesLines->first();
                    $tickets_price += $priceLine['modifier_price'];
                }
            }

            if ($price->prc_model_type == 1) {
                $max_draws = 4 * $lottery->days_to_play();
                $rules = [
                    'draws' => 'required|integer|min:1|max:'.$max_draws,
                ];
                $this->validate($request, $rules);
                if ($lottery->lot_id == Lottery::$LA_PRIMITIVA && $request->cts_day_to_play == 7) {
                    $request->draws *= 2;
                }
                $tickets       = $price->prc_draws * $request->draws;
                $tickets_price *= $request->draws;
            } else {
                $tickets = $price->prc_draws;
            }




            $tickets_by_draw = $request->cts_ticket_byDraw ? $request->cts_ticket_byDraw : $cart_subscription->cts_ticket_byDraw;
            $cart_subscription->cts_subExtension = $price->prc_time;
            /* Si no es renovable, no es renovable, sino me fijo si viene el campo (Mainly orca) o lo dejo renovable*/
            $cart_subscription->cts_renew = ($price->prc_draws == 1 || $price->prc_draws == 2 || $price->prc_model_type == 1) ? 1 : ($request->has("cts_renew") ? $request->cts_renew : 0);
            $cart_subscription->cts_tickets = $tickets * $tickets_by_draw;
            $cart_subscription->cts_price = $tickets_price * $tickets_by_draw;
            $cart_subscription->cts_day_to_play = $request->has("cts_day_to_play") ? $request->get("cts_day_to_play") : $cart_subscription->cts_day_to_play;
            $cart_subscription->cts_prc_id = $price->prc_id;
            $cart->crt_total += $cart_subscription->cts_price;
            $this->cartAmounts($cart);
        }
        if ($request->cts_ticket_byDraw && $request->cts_ticket_byDraw != $cart_subscription->cts_ticket_byDraw) {
            $cart_subscription->cts_ticket_byDraw = $request->cts_ticket_byDraw;
        }
        /** Si viene que hay que pausar */
        if($request->has("cts_paused") && $request->get("cts_paused")){
            $cart_subscription->cts_ticket_nextDraw = 0;
        }else{ /* Sino se setea igual que tickets_by_draw*/
            $tickets_by_draw = $request->cts_ticket_byDraw ? $request->cts_ticket_byDraw : $cart_subscription->cts_ticket_byDraw;
            $cart_subscription->cts_ticket_nextDraw = $tickets_by_draw;
        }

        if($request->cts_pck_type !== null) {
            foreach ($cart_subscription_picks as $cart_subscription_pick) {
                $cart_subscription_pick->cts_id = $cart_subscription->cts_id;
                $cart_subscription_pick->save();
            }
        }

        /*
       * Cash4Life
       */
        if($cart_subscription->lot_id == Lottery::$CASH4LIFE_ID ){
            $first_day_to_play = LotteryFirstDayToPlay::where("cts_id", "=", $cart_subscription->cts_id)->first();
            if( $request->has("first_date_to_play")){
                $first_day_to_play->first_datetoplay = $request->first_date_to_play;
                $first_day_to_play->save();

            }

            $cts_dtp = ($request->draws != 1) ? 7 :
                Carbon::createFromFormat("Y-m-d", $first_day_to_play->first_datetoplay)->dayOfWeek;

            /**
             * Para Cash4Life
             */
            if($request->lot_id == Lottery::$CASH4LIFE_ID){
                if(!$request->has("first_date_to_play") || !$request->first_date_to_play){
                    $request->first_date_to_play = Carbon::tomorrow()->format("Y-m-d");
                }
                $cts_dtp = ($request->draws != 1) ? 7 :
                    Carbon::createFromFormat("Y-m-d", $request->first_date_to_play)->dayOfWeek;

                $request->merge(["cts_day_to_play" => $cts_dtp]);
            }

            $cart_subscription->cts_day_to_play = $cts_dtp;
            $cart_subscription->save();
        }


        $cart_subscription->save();
        $cart_subscription->load('cart_subscription_picks');
        $request = request();
        $request->merge(['pixel' => $cart_subscription->cart->cart_step1()]);
        $dataLog['cart_subscription'] = $cart_subscription->toArray();
        $dataLog['cart'] = $cart_subscription->cart->toArray();
        $cart = $cart_subscription->cart;
        $this->cartAmounts($cart);
        $this->sendLogConsoleService->execute(
            $request,
            'cart-subscription-after-operation',
            'access',
            '',
            $dataLog
        );
        return $this->showOne($cart_subscription->cart);
    }

    public function validateOnlyModifier($cart_subscription)
    {

        $count = 0;
        if($cart_subscription->cts_modifier_1 == 1){
            $count ++;
        }
        if($cart_subscription->cts_modifier_2 == 1){
            $count ++;
        }
        if($cart_subscription->cts_modifier_3 == 1){
            $count ++;
        }
        if($count>1){
            throw new UnprocessableEntityHttpException(__('accepted only modifier'), null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Core\Carts\Models\CartSubscription $cart_subscription
     * @return \Illuminate\Http\Response
     */
    /**
     * @SWG\Delete(
     *   path="/cart_lotteries/{cart_lottery}",
     *   summary="Delete Cart Lottery",
     *   tags={"Cart Lotteries"},
     *   @SWG\Parameter(
     *     name="cart_lottery",
     *     in="path",
     *     description="Cart Lottery Id.",
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
     * @param CartSubscription $cart_subscription
     * @return bool|\Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(CartSubscription $cart_subscription) {
        $request = request();
        $dataLog['cart_subscription'] = $cart_subscription->toArray();
        $dataLog['cart'] = $cart_subscription->cart->toArray();
        $this->sendLogConsoleService->execute(
            $request,
            'cart-subscription-before-operation',
            'access',
            '',
            $dataLog
        );
        $validation = $this->validateCart($cart_subscription->crt_id);
        if ($validation) return $validation;
        $lock = $this->check_for_cart_lock($cart_subscription->crt_id);
        if ($lock) return $lock;
        $cart_subscription->cart_subscription_picks->each(function (CartSubscriptionPick $item) {
            $item->delete();
        });

        if($cart_subscription->first_day_to_play)
            $cart_subscription->first_day_to_play->delete();

        $cart = $cart_subscription->cart;
        $cart->crt_total -= $cart_subscription->cts_price;
        CartSubscription::where('crt_id', $cart_subscription->crt_id)
            ->where('cts_id', $cart_subscription->cts_id)
            ->delete();
        $this->cartAmounts($cart);
        $request->merge(['pixel' => $cart->cart_step1()]);
        $cartSubscription = CartSubscription::where('cts_id', $cart_subscription->cts_id)
            ->first();
        $cartSubscription = $cartSubscription === null ? [] : $cartSubscription->toArray();
        $dataLog['cart_subscription'] = $cartSubscription;
        $dataLog['cart'] = $cart->toArray();
        $this->cartAmounts($cart);
        $this->sendLogConsoleService->execute(
            $request,
            'cart-subscription-after-operation',
            'access',
            '',
            $dataLog
        );
        return $this->showOne($cart);
    }

}
