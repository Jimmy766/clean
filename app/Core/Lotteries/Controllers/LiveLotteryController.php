<?php

    namespace App\Core\Lotteries\Controllers;

    use App\Core\Clients\Models\ClientProduct;
    use App\Core\Lotteries\Models\LiveLottery;
    use App\Core\Lotteries\Transforms\LiveLotteryTransformer;
    use App\Http\Controllers\ApiController;
    use Illuminate\Http\Request;

    class LiveLotteryController extends ApiController {
        public function __construct() {
            parent::__construct();
            $this->middleware('auth:api')->except('index', 'show', 'results_live', 'draws_play');
            $this->middleware('client.credentials')->only('index', 'show', 'results_live', 'draws_play');
            $this->middleware('transform.input:' . LiveLotteryTransformer::class);
        }

        /**
         * @SWG\Get(
         *   path="/live_lotteries",
         *   summary="Show live lotteries list ",
         *   tags={"Live Lotteries"},
         *   security={
         *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
         *     {"password": {}, "user_ip":{},  "Content-Language":{}},
         *   },
         *   @SWG\Response(
         *     response=200,
         *     description="Successful operation",
         *     @SWG\Schema(
         *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/LiveLottery")),
         *     ),
         *   ),
         *   @SWG\Response(response=401, ref="#/responses/401"),
         *   @SWG\Response(response=403, ref="#/responses/403"),
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
            $live_lotteries = LiveLottery::with(['active_bet', 'modifiers'])->where('lot_live', '=', 1)->whereIn('lot_id', self::client_live_lotteries(1)->pluck('product_id'))->get();;
            return $this->showAllNoPaginated($live_lotteries);
        }

        /**
         * @SWG\Get(
         *   path="/live_lotteries/{live_lottery}",
         *   summary="Show lottery details ",
         *   tags={"Live Lotteries"},
         *   @SWG\Parameter(
         *     name="live_lottery",
         *     in="path",
         *     description="Live Lottery Id.",
         *     required=true,
         *     type="integer"
         *   ),
         *   security={
         *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
         *     {"password": {}, "user_ip":{},  "Content-Language":{}},
         *   },
         *   @SWG\Response(
         *     response=200,
         *     description="Successful operation",
         *     @SWG\Schema(
         *         @SWG\Property(
         *         property="data",
         *         allOf={
         *          @SWG\Schema(ref="#/definitions/LiveLottery"),
         *         }
         *       ),
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
         * Display the specified resource.
         *
         * @param  \App\Core\Lotteries\Models\LiveLottery $liveLottery
         *
         * @return \Illuminate\Http\JsonResponse
         */
        public function show(LiveLottery $liveLottery) {
            return self::client_live_lotteries(1)->pluck('product_id')->contains($liveLottery->lot_id) ? $this->showOne($liveLottery) : $this->errorResponse(trans('lang.lottery_live_forbidden'), 403);
        }

        /**
         * @SWG\Post(
         *   path="/live_lotteries/results/{live_lottery}",
         *   summary="Show lottery results by dates",
         *   tags={"Live Lotteries"},
         *   consumes={"multipart/form-data"},
         *   @SWG\Parameter(
         *     name="live_lottery",
         *     in="path",
         *     description="Live Lottery Id.",
         *     required=true,
         *     type="integer"
         *   ),
         *   @SWG\Parameter(
         *     name="draw_date",
         *     in="formData",
         *     description="Draw date.",
         *     required=false,
         *     type="string",
         *     format="date-time",
         *   ),
         *   security={
         *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
         *     {"password": {}, "user_ip":{},  "Content-Language":{}},
         *   },
         *   @SWG\Response(
         *     response=200,
         *     description="Successful operation",
         *     @SWG\Schema(
         *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/LiveDraw")),
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
        /**
         * @param Request                                $request
         * @param \App\Core\Lotteries\Models\LiveLottery $liveLottery
         *
         * @return \Illuminate\Http\JsonResponse
         * @throws \Exception
         */
        public function results_live(Request $request, LiveLottery $liveLottery) {
            if (!self::client_live_lotteries(0,1)->pluck('product_id')->contains($liveLottery->lot_id)) {
                return $this->errorResponse(trans('lang.lottery_live_forbidden'), 403);
            }
            $rules = [
                'draw_date' => 'date',
            ];
            $this->validate($request, $rules);

            $draws = $liveLottery->result($request->draw_date);
            return $draws ? $this->showAllNoPaginated($draws) : $this->showMessage(trans('lang.no_data'));
        }

        /**
         * @SWG\Post(
         *   path="/live_lotteries/draws/{live_lottery}",
         *   summary="Show next available draws to play",
         *   tags={"Live Lotteries"},
         *   consumes={"multipart/form-data"},
         *   @SWG\Parameter(
         *     name="live_lottery",
         *     in="path",
         *     description="Live Lottery Id.",
         *     required=true,
         *     type="integer"
         *   ),
         *   security={
         *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
         *     {"password": {}, "user_ip":{},  "Content-Language":{}},
         *   },
         *   @SWG\Response(
         *     response=200,
         *     description="Successful operation",
         *     @SWG\Schema(
         *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/LiveDraw")),
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
        /**
         * @param Request                                $request
         * @param \App\Core\Lotteries\Models\LiveLottery $liveLottery
         *
         * @return \Illuminate\Http\JsonResponse
         * @throws \Exception
         */

        public function draws_play(Request $request, LiveLottery $liveLottery) {
            if (!self::client_live_lotteries(0,1)->pluck('product_id')->contains($liveLottery->lot_id)) {
                return $this->errorResponse(trans('lang.lottery_live_forbidden'), 403);
            }
            $draws = $liveLottery->getValidDrawsPlay();
            return $draws ? $this->showAllNoPaginated($draws) : $this->showMessage(trans('lang.no_data'));
        }

    }
