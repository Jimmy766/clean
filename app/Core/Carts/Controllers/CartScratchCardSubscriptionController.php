<?php

    namespace App\Core\Carts\Controllers;

    use App\Core\Carts\Models\Cart;
    use App\Core\Carts\Models\CartScratchCardSubscription;
    use App\Core\ScratchCards\Models\ScratchCardPrice;
    use App\Core\Base\Services\FastTrackLogService;
    use App\Core\Base\Traits\CartUtils;
    use App\Core\Carts\Transforms\CartScratchCardSubscriptionTransformer;
    use App\Http\Controllers\ApiController;
    use Illuminate\Http\Request;
    use Illuminate\Validation\Rule;

    class CartScratchCardSubscriptionController extends ApiController
    {
        use CartUtils;
        /**
         * CartSubscriptionController constructor.
         */
        public function __construct() {
            parent::__construct();
            $this->middleware('client.credentials');
            $this->middleware('transform.input:' . CartScratchCardSubscriptionTransformer::class)->only(['store', 'update']);
        }

        /**
         * @SWG\Post(
         *   path="/cart_scratch_cards",
         *   summary="Create cart scratch card",
         *   tags={"Cart Scratch Cards"},
         *   consumes={"multipart/form-data"},
         *   @SWG\Parameter(
         *     name="cart_id",
         *     in="formData",
         *     description="Cart Id.",
         *     required=true,
         *     type="integer"
         *   ),
         *   @SWG\Parameter(
         *     name="scratch_card",
         *     in="formData",
         *     description="Scratch Cards Id.",
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
        /**
         * Store a newly created resource in storage.
         *
         * @param  \Illuminate\Http\Request $request
         *
         * @return \Illuminate\Http\JsonResponse
         */
        public function store(Request $request) {

            $rules = [
//                validate cart exist
                'crt_id' => 'required|integer|exists:mysql_external.carts,crt_id',
//                validate can play lottery
                'scratches_id' => 'required|integer|exists:mysql_external.scratches,id|' . Rule::in(self::client_scratch_cards(1)->pluck('product_id')),
//                validate draws exists and from the lottery
                'prc_id' => 'required|integer|' . Rule::exists('mysql_external.scratches_prices', 'prc_id')->where('scratches_id', $request->scratches_id),
            ];
            $this->validate($request, $rules);
//          validate cart from user and valid in site
            $validation = $this->validateCart($request->crt_id);
            if ($validation) return $validation;
            $lock = $this->check_for_cart_lock($request->crt_id);
            if ($lock) return $lock;
            $cart = Cart::where('crt_id', $request->crt_id)->first();
            $scratch_card_price = ScratchCardPrice::where('prc_id', $request->prc_id)->first();

            $cart_scratch_card_subscription = new CartScratchCardSubscription();
            $cart_scratch_card_subscription->crt_id = $request->crt_id;
            $cart_scratch_card_subscription->scratches_id = $request->scratches_id;
            $cart_scratch_card_subscription->cts_prc_id = $request->prc_id;
            $cart_scratch_card_subscription->cts_rounds = $scratch_card_price->rounds;
            $cart_scratch_card_subscription->cts_price = $scratch_card_price->price_line['price'];
            $cart_scratch_card_subscription->cts_rounds_free = 0;
            $cart_scratch_card_subscription->bonus_id = 0;
            $cart_scratch_card_subscription->save();
//            update cart total
            $cart->increment('crt_total', $cart_scratch_card_subscription->cts_price);

            $this->cartAmounts($cart);
            $request->merge(['pixel' => $cart->cart_step1()]);
            return $this->showOne($cart, 201);
        }

        /**
         * @SWG\Put(
         *   path="/cart_scratch_cards/{cart_scratch_card}",
         *   summary="Update cart scratch card",
         *   tags={"Cart Scratch Cards"},
         *   consumes={"application/x-www-form-urlencoded"},
         *   @SWG\Parameter(
         *     name="cart_scratch_card",
         *     in="path",
         *     description="Cart scratch card id.",
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

        public function update(Request $request, CartScratchCardSubscription $cart_scratch_card) {
            $rules = [
//                validate draws exists and from the lottery
                'prc_id' => 'required|integer|' . Rule::exists('mysql_external.scratches_prices', 'prc_id')->where('scratches_id', $cart_scratch_card->scratches_id),
            ];
            $this->validate($request, $rules);
//          validate cart from user and valid in site
            $validation = $this->validateCart($cart_scratch_card->crt_id);
            if ($validation) return $validation;
            $lock = $this->check_for_cart_lock($cart_scratch_card->crt_id);
            if ($lock) return $lock;
            $cart = Cart::where('crt_id', $cart_scratch_card->crt_id)->first();
            $cart->decrement('crt_total', $cart_scratch_card->cts_price);

            $scratch_card_price = ScratchCardPrice::where('prc_id', $request->prc_id)->first();

            $cart_scratch_card->cts_prc_id = $request->prc_id;
            $cart_scratch_card->cts_rounds = $scratch_card_price->rounds;
            $cart_scratch_card->cts_price = $scratch_card_price->price_line['price'];
            $cart_scratch_card->cts_rounds_free = 0;
            $cart_scratch_card->bonus_id = 0;
            $cart_scratch_card->save();
//            update cart total
            $cart->increment('crt_total', $cart_scratch_card->cts_price);

            $this->cartAmounts($cart);
            $request->merge(['pixel' => $cart->cart_step1()]);
            return $this->showOne($cart, 201);
        }

        /**
         * @SWG\Get(
         *   path="/cart_scratch_cards/{cart_scratch_card}",
         *   summary="Show cart scratch card details",
         *   tags={"Cart Scratch Cards"},
         *   @SWG\Parameter(
         *     name="cart_scratch_card",
         *     in="path",
         *     description="Cart scratch Cards Id.",
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
         *       @SWG\Property(property="data", allOf={ @SWG\Schema(ref="#/definitions/CartScratchCardSubscription"), }),
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
         * Display a listing of the resource.
         *
         * @return \Illuminate\Http\JsonResponse
         */
        public function show(CartScratchCardSubscription $cart_scratch_card) {
            $validation = $this->validateCart($cart_scratch_card->crt_id);
            if ($validation) return $validation;
            return $this->showOne($cart_scratch_card);
        }

        /**
         * @SWG\Delete(
         *   path="/cart_scratch_cards/{cart_scratch_card}",
         *   summary="Delete cart scratch card",
         *   tags={"Cart Scratch Cards"},
         *   @SWG\Parameter(
         *     name="cart_scratch_card",
         *     in="path",
         *     description="Cart scratch Cards Id.",
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
        /**
         * Remove the specified resource from storage.
         *
         * @param  \App\Core\Carts\Models\CartScratchCardSubscription $cart_scratch_card
         *
         * @return \Illuminate\Http\JsonResponse
         * @throws \Exception
         */
        public function destroy(CartScratchCardSubscription $cart_scratch_card) {
            $validation = $this->validateCart($cart_scratch_card->crt_id);
            if ($validation) return $validation;
            $lock = $this->check_for_cart_lock($cart_scratch_card->crt_id);
            if ($lock) return $lock;
            $cart = $cart_scratch_card->cart;
//            update cart total
            $cart->decrement('crt_total', $cart_scratch_card->cts_price);
            $cart_scratch_card->delete();
            $this->cartAmounts($cart);
            $request = request();
            $request->merge(['pixel' => $cart->cart_step1()]);
            return $this->showOne($cart);
        }
    }
