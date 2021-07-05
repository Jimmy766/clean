<?php

    namespace App\Core\ScratchCards\Controllers;

    use App\Core\ScratchCards\Models\ScratchCardSubscription;
    use App\Core\ScratchCards\Transforms\ScratchCardSubscriptionTransformer;
    use App\Http\Controllers\ApiController;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;

    class ScratchCardSubscriptionController extends ApiController
    {
        public function __construct() {
            parent::__construct();
            $this->middleware('auth:api');
            $this->middleware('transform.input:' . ScratchCardSubscriptionTransformer::class);
        }

        /**
         * @SWG\Get(
         *   path="/scratch_card_subscriptions",
         *   summary="Show user scratch cards subscriptions",
         *   tags={"Subscriptions"},
         *   security={
         *     {"password": {}, "user_ip":{},  "Content-Language":{}},
         *   },
         *   @SWG\Parameter(
         *     name="status",
         *     in="query",
         *     description="Subscription status (active, expired)",
         *     required=false,
         *     type="string"
         *   ),
         *
         *   @SWG\Response(
         *     response=200,
         *     description="Successful operation",
         *     @SWG\Schema(
         *         @SWG\Property(property="data", type="array",
         *     @SWG\Items(ref="#/definitions/ScratchCardSubscription")),
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
        public function index() {
            $inactive= ScratchCardSubscription::with(['cart_subscription', 'movements', 'scratch_card'])
                ->where('usr_id', '=', request()->user()->usr_id)
                ->whereRaw('((sub_rounds > 0 and ((sub_rounds + sub_rounds_extra) = sub_emitted)) or (sub_rounds_free > 0 and (sub_rounds_free = sub_emitted_free)) or sub_status = 2)')
                ->orderBy('sub_buydate', 'desc')
                ->limit(config('constants.inactive_qty'))
                ->get();
            $active= ScratchCardSubscription::with(['cart_subscription', 'movements', 'scratch_card'])
                ->where('usr_id', '=', request()->user()->usr_id)
                ->whereRaw('((((sub_rounds + sub_rounds_extra) > sub_emitted) or (sub_rounds_free > sub_emitted_free)) and sub_status != 2)')
                ->orderBy('sub_buydate', 'desc')
                ->get();
            $scratchcard_subscriptions = $active->concat($inactive)->sortByDesc('sub_buydate');
            return $this->showAllNoPaginated($scratchcard_subscriptions);
        }


        /**
         * @SWG\Get(
         *   path="/scratch_card_subscriptions/{scratch_card_subscription}",
         *   summary="Show user scratch card subscription detail",
         *   tags={"Subscriptions"},
         *   security={
         *     {"password": {}, "user_ip":{},  "Content-Language":{}},
         *   },
         *   @SWG\Parameter(
         *     name="scratch_card_subscription",
         *     in="path",
         *     description="Scratch card Subscription Id.",
         *     required=true,
         *     type="integer"
         *   ),
         *   @SWG\Response(
         *     response=200,
         *     description="Successful operation",
         *     @SWG\Schema(@SWG\Property(
         *       property="data",
         *       allOf={
         *          @SWG\Schema(ref="#/definitions/ScratchCardSubscription"),
         *       }
         *     ),),
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
         * @param  \App\Core\ScratchCards\Models\ScratchCardSubscription $scratch_card_subscription
         *
         * @return \Illuminate\Http\JsonResponse
         */
        public function show(ScratchCardSubscription $scratch_card_subscription) {
            if ($scratch_card_subscription->scratch_card == null) {
                return $this->errorResponse(trans('lang.no_scratch_subscription'), 422);
            }
            if (request()->user()->usr_id === $scratch_card_subscription->usr_id) {
                return $this->showOne($scratch_card_subscription);
            } else {
                return $this->errorResponse(trans('lang.subscription_forbidden'), 422);
            }
        }



        /**
         * @SWG\Post(
         *   path="/scratch_card_subscriptions/can_play/{scratch_card_subscription}",
         *   summary="Can play scratch card",
         *   tags={"Subscriptions"},
         *   @SWG\Parameter(
         *     name="scratch_card_subscription",
         *     in="path",
         *     description="Scratch card Subscription Id.",
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
         *       @SWG\Property(property="data", type="array",
         *          @SWG\Items(
         *            @SWG\Property(property="can_play", type="boolean")
         *          )
         *       ),
         *     ),
         *   ),
         *   @SWG\Response(response=401, ref="#/responses/401"),
         *   @SWG\Response(response=422, ref="#/responses/422"),
         *   @SWG\Response(response=403, ref="#/responses/403"),
         *   @SWG\Response(response=500, ref="#/responses/500"),
         * )
         *
         */
        /**
         * @return \Illuminate\Http\JsonResponse
         */
        public function can_play(ScratchCardSubscription $scratch_card_subscription) {
            if ($scratch_card_subscription->scratch_card == null) {
                return $this->errorResponse(trans('lang.no_scratch_subscription'), 422);
            }
            if (!self::client_scratch_cards(1)->pluck('product_id')->contains($scratch_card_subscription->scratch_card->id) )
                return $this->errorResponse(trans('lang.scratch_forbidden'), 403);
            if (request()->user()->usr_id === $scratch_card_subscription->usr_id) {
                return $this->successResponse(['data' => ['can_play' => $scratch_card_subscription->canPlay()]]);
            } else {
                return $this->errorResponse(trans('lang.subscription_forbidden'), 422);
            }
        }

        public function src(Request $request, ScratchCardSubscription $scratch_card_subscription) {
            $rules = [
//                validate
                'is_mobile' => 'required|boolean',
            ];
            $this->validate($request, $rules);
            if ($scratch_card_subscription->scratch_card == null) {
                return $this->errorResponse(trans('lang.no_scratch_subscription'), 422);
            }
            if (!self::client_scratch_cards(1)->pluck('product_id')->contains($scratch_card_subscription->scratch_card->id) )
                return $this->errorResponse(trans('lang.scratch_forbidden'), 403);
            if (Auth::user()->usr_id === $scratch_card_subscription->usr_id && $scratch_card_subscription->canPlay()) {
                $language = $this->getLanguage();
                $data = $scratch_card_subscription->srcReal($request->is_mobile, $language);
                if (isset($data->Url))
                    return $this->successResponse(['data' => (array)$data]);
                else
                    return $this->errorResponse($data->error, 422);
            } else {
                return $this->errorResponse(trans('lang.subscription_forbidden'), 422);
            }
        }
    }
