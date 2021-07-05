<?php

namespace App\Core\Lotteries\Controllers;


use App\Core\Lotteries\Models\Lottery;
use App\Core\Lotteries\Services\AllLotteriesActiveService;
use App\Core\Lotteries\Services\CheckLotteriesNotExceedLimitJackpotService;
use App\Core\Lotteries\Services\GetLotteriesAndCheckInsureBlackListService;
use App\Core\Syndicates\Models\Syndicate;
use App\Core\Base\Traits\CartUtils;
use App\Core\Base\Traits\Pixels;
use App\Core\Rapi\Transforms\DrawResultTransformer;
use App\Core\Lotteries\Transforms\LotteryAlertListTransformer;
use App\Core\Lotteries\Transforms\LotteryListTransformer;
use App\Core\Lotteries\Transforms\LotteryTransformer;
use App\Core\Users\Models\User;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class LotteryController extends ApiController
{

    use Pixels;

    /**
     * @var \App\Core\Lotteries\Services\GetLotteriesAndCheckInsureBlackListService
     */
    private $getLotteriesAndCheckInsureBlackListService;
    /**
     * @var CheckLotteriesNotExceedLimitJackpotService
     */
    private $checkLotteriesNotExceedLimitJackpotService;
    /**
     * @var AllLotteriesActiveService
     */
    private $allLotteriesActiveService;

    public function __construct(
        GetLotteriesAndCheckInsureBlackListService $getLotteriesAndCheckInsureBlackListService,
        CheckLotteriesNotExceedLimitJackpotService $checkLotteriesNotExceedLimitJackpotAndGetUrlService,
        AllLotteriesActiveService $allLotteriesActiveService
    ) {
        parent::__construct();
        $this->middleware('auth:api')->except('index', 'show', 'draw_result', 'draw_results', 'prices_list', 'lottery_dates', 'lottery_prizes', 'levels');
        $this->middleware('client.credentials')->only('index', 'show', 'draw_result', 'draw_results', 'prices_list', 'lottery_dates', 'lottery_prizes', 'levels');
        $this->middleware('transform.input:' . LotteryTransformer::class);
        $this->getLotteriesAndCheckInsureBlackListService = $getLotteriesAndCheckInsureBlackListService;
        $this->checkLotteriesNotExceedLimitJackpotService = $checkLotteriesNotExceedLimitJackpotAndGetUrlService;
        $this->allLotteriesActiveService = $allLotteriesActiveService;
    }

    use CartUtils;

    /**
     * @SWG\Get(
     *   path="/lotteries",
     *   summary="Show lotteries list ",
     *   tags={"Lotteries"},
     *   @SWG\Parameter(
     *     name="sort_by_asc",
     *     in="query",
     *     description="Attribute to Sort in Ascending Order",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="sort_by_desc",
     *     in="query",
     *     description="Attribute to Sort in Descending Order",
     *     required=false,
     *     type="string"
     *   ),
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/LotteryList")),
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

        $lotteries = $this->allLotteriesActiveService->execute();

        if ($lotteries->isNotEmpty()) {
            $lotteries->first()->transformer = LotteryListTransformer::class;
        }

        return $this->showAllNoPaginated($lotteries);
    }

    /**
     * @SWG\Get(
     *   path="/users/alerts/lotteries",
     *   summary="Show lotteries alert list",
     *   tags={"Users"},
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/LotteryAlertList")),
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
    public function alerts_mails() {
        $product_ids = self::client_lotteries(1,0)->pluck('product_id');
        $lotteries = Lottery::where('lot_active', '=', 1)
            ->whereIn('lot_id', $product_ids)
            ->get();
        if ($lotteries->isNotEmpty()) {
            $lotteries->first()->transformer = LotteryAlertListTransformer::class;
        }

        return $this->showAllNoPaginated($lotteries);
    }

    public function index2() {
        $url = "http://ip2loc.trillonarios.com/api.php";
            $r = "?c_ip=" . request()->user_ip;

            $ch = curl_init($url.$r);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);

            $response = curl_exec($ch);
            $Error = curl_error($ch);

            if (!curl_errno($ch)) {
                curl_close($ch);
            } else {
                dd($Error);
                dd( "Falla el curl a ip2location");
            }

            if ($Error == null || $Error == "") {
                $country = json_decode($response,TRUE);
                $country_code = $country['code'];
            }

        return $country_code;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Core\Lotteries\Models\Lottery $lottery
     * @return \Illuminate\Http\Response
     */
    /**
     * @SWG\Get(
     *   path="/lotteries/{lottery}",
     *   summary="Show lottery details ",
     *   tags={"Lotteries"},
     *   @SWG\Parameter(
     *     name="lottery",
     *     in="path",
     *     description="Lottery Id.",
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
     *         allOf={
     *          @SWG\Schema(ref="#/definitions/Lottery"),
     *         }
     *       ),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     * @param $lottery
     * @return \Illuminate\Http\JsonResponse
     */

    public function show($lottery) {
        $relations = [
            'draw_active.lottery',
            'draws',
            'lotteriesBoostedJackpot.lotteriesModifier',
            'prices',
            'prev_draw',
            'draw_active',
            'routingFriendly',
        ];
        $arrayLottery = [ $lottery ];
        $lotteries    = $this->allLotteriesActiveService->execute($arrayLottery, $relations);
        $lottery      = $lotteries->first();
        /** @var Lottery $lottery */
        if ( $lottery === null) {
            return $this->errorResponse( trans( 'lang.lottery_forbidden' ), Response::HTTP_FORBIDDEN );
        }
        $prices       = $lottery->prices;
        /**
         * Cash4Life solo tiene un precio.
         */
        if ($prices->pluck('prc_model_type')->contains(0) || $lottery->isCash4Life()) {
            $price = $prices->first();
        } else {
            $price = $prices->where('prc_model_type', '=', 2)->first();
        }
        if(!$price || !isset($price->prc_id)){
            return $this->showMessage(trans('lottery.no_price'), 403);
        }
        $price_id = $price->prc_id;
        $price_line_id = $price->price_line['identifier'];
        $product = [
            'id' => $lottery->lot_id,
            'name' => $lottery->name,
        ];
        $request = request();
        $request->merge(['pixel' => $this->retargeting(1, $product, $price_id, $price_line_id)]);

        return self::client_lotteries(1,0)->pluck('product_id')
            ->contains($lottery->lot_id) ? $this->showOne($lottery) : $this->errorResponse(trans('lang.lottery_forbidden'), 403);
    }

    /**
     * @SWG\Get(
     *   path="/lotteries/last_results/list",
     *   summary="Show lotteries last results",
     *   tags={"Lotteries"},
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/DrawResult")),
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
     * @param Request $request
     * @return mixed
     */
    public function draw_results(Request $request) {
        $clientId = $request[ "oauth_client_id" ];
        $nameCache = 'draw_results_'.$clientId;
        if (Cache::has($nameCache)) {
            //return from cache
            $draws = Cache::get($nameCache);
            return $draws ? $this->showAllNoPaginated($draws) : $this->showMessage(trans('lang.no_data'));
        }

        $lotteries = Lottery::with(["prev_draw.lottery.region.continent", "prev_draw.lottery.region.country", 'routingFriendly'])
            ->where('lot_active', 1)
            ->whereIn('lot_id', self::client_lotteries(0,1)->pluck('product_id'))
            ->get();
        $syndicates = Syndicate::with('syndicate_lotteries.lottery')
            ->where('active', 1)
            ->whereIn('id', self::client_syndicates(0, 1)->pluck('product_id'))
            ->get();
        $syndicates->each(function ($item) use ($lotteries) {
            $item->syndicate_lotteries->each(function ($item) use ($lotteries) {
                if (!$lotteries->contains($item->lottery)) {
                    $lotteries->push($item->lottery);
                }
            });
        });
        $draws = collect([]);
        $lotteries->each(function ($item) use ($draws) {
            if ($item->prev_draw !== null) {
                $draws->push($item->prev_draw);
            }
        });
        if($draws->count() !== 0){
            $draws->first()->transformer = DrawResultTransformer::class;
        }
        // half hour cacheing result
        $expiresIn = config('constants.cache_half_hour');
        if (isset($expiresIn)) {
            Cache::put($nameCache, $draws, $expiresIn);
        }

        return $draws ? $this->showAllNoPaginated($draws) : $this->showMessage(trans('lang.no_data'));
    }

    /**
     * @SWG\Post(
     *   path="/lotteries/draw_result/{lottery}",
     *   summary="Show lottery results by dates",
     *   tags={"Lotteries"},
     *   consumes={"multipart/form-data"},
     *   @SWG\Parameter(
     *     name="lottery",
     *     in="path",
     *     description="Lottery Id.",
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
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(
     *         property="data",
     *         allOf={
     *          @SWG\Schema(ref="#/definitions/DrawResult"),
     *         }
     *       ),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     * @param Request $request
     * @param         $lottery
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function draw_result(Request $request, $lottery) {
        $lottery = $this->getLotteryByCache($lottery);
        if (self::client_lotteries(0,1)->pluck('product_id')->contains($lottery->lot_id)) {
            $this->errorResponse(trans('lang.lottery_forbidden'), 403);
        }


        $rules = [
            'draw_date' => 'date|' . Rule::exists('mysql_external.draws')->where('draw_status', 1)->where('lot_id', $lottery->lot_id),
        ];
        $this->validate($request, $rules);
        $draw = $request->draw_date ? $lottery->oldDraws()->where('draw_date', '=', $request->draw_date)->first() :
            $lottery->oldDraws()->first();
        if ($draw) {
            $draw->transformer = DrawResultTransformer::class;
        }

        return $draw ? $this->showOne($draw) : $this->showMessage(trans('lang.no_data'));
    }

    /**
     * @SWG\Get(
     *   path="/lotteries/levels/{lottery}",
     *   summary="Show lottery levels",
     *   tags={"Lotteries"},
     *   @SWG\Parameter(
     *     name="lottery",
     *     in="path",
     *     description="Lottery Id.",
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
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/LotteryLevel")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     * @param $lottery
     * @return \Illuminate\Http\JsonResponse
     */
    public function levels($lottery) {
        $lottery = $this->getLotteryByCache($lottery);

        return $this->showAllNoPaginated($lottery->levels);
    }

    private function getLotteryByCache($lottery)
    {
        $reLottery = Lottery::query()
            ->where('lot_id', $lottery)
            ->with([ 'prices.price_lines', 'draw_active' ])
            ->firstFromCache();
        if ($reLottery === null) {
            throw new UnprocessableEntityHttpException(
                __('lottery dont exist'),
                null,
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return $reLottery;
    }

    /**
     * @SWG\Get(
     *   path="/lotteries/prices_list/{lottery}",
     *   summary="Show lottery prices ",
     *   tags={"Lotteries"},
     *   @SWG\Parameter(
     *     name="lottery",
     *     in="path",
     *     description="Lottery Id.",
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
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/LotteryPrice")),
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
     * @param Lottery $lottery
     * @return \Illuminate\Http\JsonResponse
     */
    public function prices_list($lottery) {
        $lottery = $this->getLotteryByCache($lottery);
        $pricesList = $lottery->prices_list;

        return $this->showAllNoPaginated($pricesList);
    }

    /**
     * @SWG\Get(
     *   path="/lotteries/lottery_dates/{lottery}",
     *   summary="Show lottery past draws dates ",
     *   tags={"Lotteries"},
     *   @SWG\Parameter(
     *     name="lottery",
     *     in="path",
     *     description="Lottery Id.",
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
     *        @SWG\Property(
     *          property="data",
     *          type="array",
     *          @SWG\Items(type="object", example="518: 2006-11-17"),
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
     * @param Lottery $lottery
     * @return \Illuminate\Http\JsonResponse
     */
    public function lottery_dates($lottery) {
        $lottery = $this->getLotteryByCache($lottery);

        return $this->successResponse(['data' => $lottery->oldDraws()->pluck('draw_date', 'draw_id')], 200);
    }

    /**
     * @SWG\Post(
     *   path="/lotteries/prizes/{lottery}",
     *   summary="Show lottery prizes",
     *   tags={"Lotteries"},
     *   @SWG\Parameter(
     *     name="lottery",
     *     in="path",
     *     description="Lottery Id.",
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
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/LotteryPrize")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     * @param Request $request
     * @param         $lottery
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function lottery_prizes(Request $request, $lottery) {
        $lottery = $this->getLotteryByCache($lottery);
        $rules = [
            'draw_date' => 'date|' . Rule::exists('mysql_external.draws')->where('draw_status', 1)->where('lot_id', $lottery->lot_id),
        ];
        $this->validate($request, $rules);
        $draw = $request->draw_date ? $lottery->oldDraws()->where('draw_date', '=', $request->draw_date)->first() :
            $lottery->oldDraws()->first();

        return $draw ? $this->showAllNoPaginated($lottery->lottery_prizes($draw)) : $this->showMessage(trans('lang.no_data'));
    }
}
