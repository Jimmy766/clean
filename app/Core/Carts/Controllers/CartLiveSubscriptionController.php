<?php

    namespace App\Core\Carts\Controllers;

    use App\Core\Carts\Models\Cart;
    use App\Core\Carts\Models\CartLiveLotterySubscription;
    use App\Core\Carts\Models\CartLiveLotterySubscriptionPick;
    use App\CartLiveLotterySubscriptionR;
    use App\Core\Lotteries\Models\LiveLottery;
    use App\Core\Base\Services\FastTrackLogService;
    use App\Core\Base\Traits\CartUtils;
    use App\Core\Carts\Transforms\CartLiveLotterySubscriptionTransformer;
    use App\Http\Controllers\ApiController;
    use Illuminate\Http\Request;
    use DB;
    use Illuminate\Validation\Rule;

    class CartLiveSubscriptionController extends ApiController {
        use CartUtils;

        /**
         * CartLiveSubscriptionController constructor.
         */
        public function __construct() {
            parent::__construct();
            $this->middleware('client.credentials');
            $this->middleware('transform.input:' . CartLiveLotterySubscriptionTransformer::class)->only(['update']);
        }

        /**
         * Store a newly created resource in storage.
         *
         * @param  \Illuminate\Http\Request $request
         *
         * @return \Illuminate\Http\Response
         */
        /**
         * @SWG\Post(
         *   path="/cart_live_lottery",
         *   summary="Create Cart Live Lottery",
         *   tags={"Cart Live Lotteries"},
         *   consumes={"application/json"},
         *   @SWG\Parameter(
         *     name="body",
         *     in="body",
         *     description="Bets",
         *     required=true,
         *     @SWG\Schema(ref="#/definitions/BetsLiveLottery")
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
        public function store(Request $request) {
            $rules = [
//                validate cart exist
                'cart' => 'required|integer|exists:mysql_external.carts,crt_id',
//                validate can play lottery
                'lottery' => 'required|integer|exists:mysql_external.lotteries,lot_id|' . Rule::in(self::client_live_lotteries(1)->pluck('product_id')),
//                validate draws exists and from the lottery
                'draws' => 'required|array|' . Rule::exists('mysql_external.draws', 'draw_id')->where('lot_id', $request->lottery),
//                validate send tickets
                'tickets' => 'required|array',
//                validate tickets have a picks
                'tickets.*.picks' => 'required|array',
            ];
            $this->validate($request, $rules);
//          validate cart from user and valid in site
            $validation = $this->validateCart($request->cart);
            if ($validation) return $validation;
            $lock = $this->check_for_cart_lock($request->cart);
            if ($lock) return $lock;
            /**
             * search for de live lottery
             *
             * @var $lottery LiveLottery
             */
            $lottery = LiveLottery::where('lot_id', '=', $request->lottery)->where('lot_live', '=', 1)->first();
            if (!$lottery) return $this->errorResponse(trans('lang.lottery_live_forbidden'), 403);
//          search for de bet configuration
            $bet = $lottery->bet;
            if (!$bet) return $this->errorResponse(trans('lang.lottery_live_bad_bet_config'), 403);

            $valid_draws_play = $lottery->getValidDrawsPlay();
            $rules = [
//                validate draws in time to play
                'draws.*' => Rule::in($valid_draws_play->pluck('draw_id')),
//                validate modifier exists and from the lottery
                'tickets.*.modifier_id' => 'required|' . Rule::exists('mysql_external.lotteries_modifiers', 'modifier_id')->where('lot_id', $request->lottery),
//                validate count picks
                'tickets.*' => [function ($att, $ticket, $fail) use ($lottery) {
                    if (count($ticket[ 'picks' ]) != $lottery->lot_pick_balls) {
                        $modifiers = $lottery->getModifierBalls()->filter(function ($value) use ($fail, $ticket) {
//                            validate balls for modifier that does not modify all the balls
                            return ($ticket[ 'modifier_id' ] == $value->modifier_id && count($ticket[ 'picks' ]) >= $value->mod_balls);
                        });
                        if ($modifiers->isEmpty()) $fail($att . ' picks is invalid.');
                    }
                }],
//                validate values of picks
                'tickets.*.picks.*' => [function ($att, $value, $fail) use ($lottery) { //validate
                    if (!is_numeric($value) || $value > $lottery->lot_maxNum) $fail($att . ' is invalid.');
                }],
//                validate value of bets
                'tickets.*.bet' => 'required|numeric|between:' . $bet[ 'min_bet' ] . ',' . $bet[ 'max_bet' ],
            ];
            $this->validate($request, $rules);
            // Exposure validation
            $limit = $lottery->extra_info->max_game_exposure;
            $modifiers_boxed = $lottery->getModifiersBoxed();
            $draws = $request->draws;
            $tickets = $request->tickets;
            $exposures = [];
            foreach ($draws as $index => $draw) {
                foreach ($tickets as $ticket) {
                    $pick = implode('', $ticket['picks']);
                    $bet = $ticket['bet'];
                    $modifier_id = $ticket['modifier_id'];
                    if (in_array($modifier_id, $modifiers_boxed)) {
                        $permutations = $lottery->getPermutations($ticket['picks']);
                        $bet = $bet / count($permutations);
                        foreach ($permutations as $permutation) {
                            $exposures[$draw][$permutation] = isset($exposures[$draw][$permutation]) ? $exposures[$draw][$permutation] + $bet : $bet;
                        }
                    } else {
                        $exposures[$draw][$pick] = isset($exposures[$draw][$pick]) ? $exposures[$draw][$pick] + $bet : $bet;
                    }
                }
            }
            $draws = $lottery->draws()->whereIn('draw_id', $draws)->with('exposures')->get();
            $draw_exposure = [];
            $error = false;
            $draw_exposure_full = 0;
            $draws->each(function($item) use (&$draw_exposure, $limit, $exposures, &$error, &$draw_exposure_full) {
                $draw_exposure = [];
                $item->exposures->each(function ($ex) use ($item, &$draw_exposure, $limit) {
                    $draw_exposure[$ex->number_played] = $ex->sold_amount;
                });

                foreach ($exposures[$item->draw_id] as $number => $amount) {
                    if (isset($draw_exposure[$number])) {
                        if (($limit) < ($draw_exposure[$number] + $amount)) {
                            $error = true;
                            $draw_exposure_full = $item->draw_external_id;
                            break;
                        }
                    } else {
                        if (($limit) < ($amount)) {
                            $error = true;
                            $draw_exposure_full = $item->draw_external_id;
                            break;
                        }
                    }
                }
            });

            if ($error) return $this->errorResponse(trans('lang.max_exposure', ['draw' => $draw_exposure_full]), 422);

            $cart = Cart::where('crt_id', $request->cart)->first();
            DB::transaction(function () use ($request, $cart, $lottery, $modifiers_boxed) {
                foreach ($request->draws as $draw) {
                    foreach ($request->tickets as $ticket) {
                        $cart_subscription = new CartLiveLotterySubscription();
                        $cart_subscription->crt_id = $request->cart;
                        $cart_subscription->lot_id = $request->lottery;
                        $cart_subscription->cts_tickets = 1;
                        $cart_subscription->cts_pck_type = 3;
                        $cart_subscription->cts_renew = 1;
                        $cart_subscription->cts_price = $ticket[ 'bet' ];
//                       if is boxed modifier find real id
                        $modifier_id = in_array($ticket[ 'modifier_id' ], $modifiers_boxed) ? $lottery->findRealModifierId($ticket[ 'picks' ]) : $ticket[ 'modifier_id' ];
                        $cart_subscription->cts_modifier_1 = $modifier_id;
                        $cart_subscription->cts_next_draw_id = $draw;
                        $cart_subscription->save();
//                        create picks
                        $cart_subscription_pick = new CartLiveLotterySubscriptionPick();
                        $cart_subscription_pick->setPicks($ticket[ 'picks' ]);
                        $cart_subscription_pick->cts_id = $cart_subscription->cts_id;
                        $cart_subscription_pick->save();
//                        update cart total
                        $cart->crt_total += $cart_subscription->cts_price;
                    }
                }
            });
            $this->cartAmounts($cart);
            $request->merge(['pixel' => $cart->cart_step1()]);
            return $this->showOne($cart, 201);
        }

        /**
         * Remove the specified resource from storage.
         *
         * @param  \App\Core\Carts\Models\CartSubscription $cartSuscription
         *
         * @return \Illuminate\Http\Response
         */

        /**
         * @SWG\Get(
         *   path="/cart_live_lottery/{cart_live_lottery}",
         *   summary="Show cart live lottery details ",
         *   tags={"Cart Live Lotteries"},
         *   @SWG\Parameter(
         *     name="cart_live_lottery",
         *     in="path",
         *     description="Cart Subscription Live Lottery Id.",
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
         *         @SWG\Property(
         *         property="data",
         *         allOf={ @SWG\Schema(ref="#/definitions/CartLiveLotterySubscription"), }
         *       ),
         *     ),
         *   ),
         *   @SWG\Response(response=401, ref="#/responses/401"),
         *   @SWG\Response(response=403, ref="#/responses/403"),
         *   @SWG\Response(response=422, ref="#/responses/422"),
         *   @SWG\Response(response=500, ref="#/responses/500"),
         * )
         *
         */
        public function show(CartLiveLotterySubscription $cart_live_lottery) {
            $validation = $this->validateCart($cart_live_lottery->crt_id);
            if ($validation) return $validation;
            return $this->showOne($cart_live_lottery);
        }

        /**
         * @SWG\Delete(
         *   path="/cart_live_lottery/{cart_live_lottery}",
         *   summary="Delete cart live lottery",
         *   tags={"Cart Live Lotteries"},
         *   @SWG\Parameter(
         *     name="cart_live_lottery",
         *     in="path",
         *     description="Cart Subscription Live Lottery Id.",
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
         *         @SWG\Property(
         *         property="data",
         *         allOf={ @SWG\Schema(ref="#/definitions/Cart"), }
         *       ),
         *     ),
         *   ),
         *   @SWG\Response(response=401, ref="#/responses/401"),
         *   @SWG\Response(response=403, ref="#/responses/403"),
         *   @SWG\Response(response=404, ref="#/responses/404"),
         *   @SWG\Response(response=500, ref="#/responses/500"),
         * )
         *
         * @throws \Exception
         */
        public function destroy(CartLiveLotterySubscription $cart_live_lottery) {
            $validation = $this->validateCart($cart_live_lottery->crt_id);
            if ($validation) return $validation;
            $lock = $this->check_for_cart_lock($cart_live_lottery->crt_id);
            if ($lock) return $lock;
            $cart = $cart_live_lottery->cart;
            $cart->crt_total -= $cart_live_lottery->cts_price;
            $cart_live_lottery->delete();
            $this->cartAmounts($cart);
            $request = request();
            $request->merge(['pixel' => $cart->cart_step1()]);
            return $this->showOne($cart);
        }
    }
