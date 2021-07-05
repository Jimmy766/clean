<?php

namespace App\Core\Raffles\Controllers;

use App\Core\Raffles\Resources\RafflesResource;
use App\Core\Raffles\Models\Raffle;
use App\Core\Raffles\Models\RaffleDraw;
use App\Core\Raffles\Models\RaffleTierTemplate;
use App\Core\Raffles\Services\AllRafflesActiveService;
use App\Core\Raffles\Services\CalculateValuePriceTierTemplateService;
use App\Core\Base\Services\ClientService;
use App\Core\Base\Services\OrcaService;
use App\Core\Base\Traits\LogCache;
use App\Core\Base\Traits\Pixels;
use App\Http\Controllers\ApiController;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class RaffleController extends ApiController
{
    use LogCache;
    use Pixels;

    /**
     * @var \App\Core\Raffles\Services\CalculateValuePriceTierTemplateService
     */
    private $calculateValuePriceTierTemplateService;
    /**
     * @var \App\Core\Raffles\Services\AllRafflesActiveService
     */
    private $allRafflesActiveService;

    public function __construct(
        CalculateValuePriceTierTemplateService $calculateValuePriceTierTemplateService,
        AllRafflesActiveService $allRafflesActiveService
    ) {
        $this->middleware('auth:api')->except(['show', 'index', 'results', 'raffle_dates', 'did_you_win', 'rafflesResultList', ]);
        $this->middleware('client.credentials')
            ->only([ 'index', 'show', 'results', 'raffle_dates', 'did_you_win', 'rafflesResultList', ]);
        $this->calculateValuePriceTierTemplateService = $calculateValuePriceTierTemplateService;
        $this->allRafflesActiveService                = $allRafflesActiveService;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @SWG\Get(
     *   path="/raffles",
     *   summary="Show raffles list ",
     *   tags={"Raffles"},
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/Raffle")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function index(Request $request) {

        $raffles = $this->allRafflesActiveService->execute($request);

        return $this->showAllNoPaginated($raffles);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Core\Raffles\Models\Raffle $raffle
     * @return \Illuminate\Http\Response
     */
    /**
     * @SWG\Get(
     *   path="/raffles/{raffle}",
     *   summary="Show raffle details ",
     *   tags={"Raffles"},
     *   @SWG\Parameter(
     *     name="raffle",
     *     in="path",
     *     description="Raffle Id.",
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
     *          @SWG\Schema(ref="#/definitions/Raffle"),
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
    public function show($idRaffle) {

        $relations = [
            'raffle_prices',
            'routingFriendly',
            'raffle_draws',
            'draw_active',
            'raffle_prices.price_lines',
            'active_draw'
        ];

        $raffle = Raffle::query()
            ->where('inf_id', $idRaffle)
            ->with($relations)
            ->firstFromCache();

        if($raffle === null){
            throw new UnprocessableEntityHttpException(__('syndicate dont exist'), null,
                Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $prices = $raffle->raffle_prices->sortBy('prc_rff_min_tickets')->values();
        $price = $prices->first();
        if($price !== null ){
            $price_id = $price->prc_rff_id;
            $price_line_id = $price->price_line['identifier'];
            $product = [
                'id' => $raffle->inf_id,
            ];
            $request = request();
            $request->merge(['pixel' => $this->retargeting(4, $product, $price_id, $price_line_id)]);
        }

        $raffleTypeReturn  = $raffle->inf_raffle_mx == 0 && self::client_raffles(1)->pluck('product_id')->contains($raffle->inf_id) &&
            $raffle->active_draw;
        return $raffleTypeReturn ? $this->showOne($raffle) : $this->showMessage(trans('lang.raffle_forbidden'));
    }

    /**
     * @SWG\Get(
     *   path="/raffles/raffle_dates/{raffle}",
     *   summary="Show raffle results dates ",
     *   tags={"Raffles"},
     *   @SWG\Parameter(
     *     name="raffle",
     *     in="path",
     *     description="Raffle Id.",
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
     *          @SWG\Items(type="object", example="31: 2011-01-15 00:00:00"),
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

    public function raffle_dates(Raffle $raffle) {
        return $this->successResponse(['data' => $raffle->dates()], 200);
    }

    /**
     * @SWG\Get(
     *   path="/raffles/results/list",
     *   summary="List raffle results",
     *   tags={"Raffles"},
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/RaffleResult")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function rafflesResultList()
    {
        $relations = [
            'datesResultRaffles.raffle_tier_results',
            'datesResultRaffles.raffleTier.raffleTierTemplates',
        ];

        $idProducts = self::client_raffles(0, 1)
            ->pluck('product_id');

        $columns = [
            'raffle_info.*',
            DB::raw("date_format(rff_playdate, '%Y-%m-%d') as format_date"),
        ];

        $raffles = Raffle::query()
            ->with($relations)
            ->whereIn('raffle_info.inf_id', $idProducts)
            ->join('raffles as r', 'r.inf_id', '=', 'raffle_info.inf_id')
            ->where('rff_view', 0)
            ->where('rff_status', 2)
            ->groupBy([ 'raffle_info.inf_id', 'format_date' ])
            ->orderByDesc('format_date')
            ->getFromCache($columns)
            ->unique('inf_id')
        ;

        $raffles = $this->calculateValuePriceTierTemplateService->execute($raffles);

        $data['raffles'] = RafflesResource::collection($raffles);

        return $this->successResponseWithMessage($data);
    }

    /**
     * @SWG\Post(
     *   path="/raffles/results/{raffle}",
     *   summary="Show raffle results",
     *   tags={"Raffles"},
     *   @SWG\Parameter(
     *     name="raffle",
     *     in="path",
     *     description="Raffle Id.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="date",
     *     in="formData",
     *     description="Raffle date.",
     *     required=false,
     *     type="string",
     *     format="date_time",
     *   ),
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/RaffleDrawResults")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function results(Request $request, Raffle $raffle) {
        $rules = [
            'date' => 'date_format:"Y-m-d"'
        ];
        $this->validate($request, $rules);
        if ($request->date) {
            $date = $request->date;
        } else {
            $date = $raffle->dates()->first();
            if ($date) {
                $date = explode(' ', $date)[0];
            } else {
                return $this->errorResponse(trans('lang.raffle_no_dates'), 422);
            }
        }
        $raffle_draw = $raffle->raffle_draws()->whereDate('rff_playdate', $date)->first();
        if (!$raffle_draw) {
            return $this->errorResponse(trans('lang.raffle_date_invalid'), 422);
        }
        $results = $this->rememberCache('raffle_results_' . $raffle_draw->rff_id .'_'.$date, Config::get('constants.cache_daily'), function () use ($raffle_draw) {
            return $raffle_draw->results()->collapse()->sortByDesc('fraction_prize')->values();
        });
        return $this->successResponse(['data' => ['date' => $date, 'raffle_draw_id' => $raffle_draw->rff_id,'results' => $results]], 200);
    }

    /**
     * @SWG\Post(
     *   path="/raffles/did_you_win/{raffle_draw}",
     *   summary="Check winner ticket",
     *   tags={"Raffles"},
     *   @SWG\Parameter(
     *     name="raffle_draw",
     *     in="path",
     *     description="Raffle draw Id.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="number",
     *     in="formData",
     *     description="Ticket number.",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="fraction",
     *     in="formData",
     *     description="Ticket fraction.",
     *     required=false,
     *     type="integer",
     *   ),
     *   @SWG\Parameter(
     *     name="series",
     *     in="formData",
     *     description="Ticket series.",
     *     required=false,
     *     type="string",
     *   ),
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(
     *         property="data",
     *         type="array",
     *         @SWG\Items(
     *           @SWG\Property(
     *             property="prize",
     *             description="Prize",
     *             type="float",
     *             example="1000"
     *           ),
     *           @SWG\Property(
     *             property="prize_name",
     *             description="Prize description",
     *             type="string",
     *             example="#LOT_NAC_RES_SPECIAL#"
     *           ),
     *         ),
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

    public function did_you_win(Request $request, RaffleDraw $raffle_draw) {
        $rules = [
            'number' => 'required|string|size:5',
            'fraction' => 'integer|min:1|max:10',
            'series' => 'string|max:2',
        ];
        $this->validate($request, $rules);
        $raffle_tier = $raffle_draw->raffle_tier;
        if (!$raffle_tier) {
            return $this->errorResponse(trans('lang.raffle_no_result'), 422);
        }
        $prizewinning_numbers = $this->rememberCache('raffle_results_' . $raffle_draw->rff_id, Config::get('constants.cache_daily'), function () use ($raffle_tier, $raffle_draw) {
            $raffle_tier_templates = $raffle_tier->raffle_tier_templates;
            $numbers = collect([]);
            $raffle_tier_templates->each(function (RaffleTierTemplate $item) use ($raffle_draw, &$numbers) {
                $number = $item->numbers($raffle_draw->rff_id);
                if ($number->isNotEmpty()) {
                    $numbers = $numbers->merge($number);
                } else {
                    $numbers->push($number);
                }
            });
            return $numbers;
        });
        $matches = $prizewinning_numbers->filter(function ($item) use ($request) {
            if ($item['value'] == $request->number) {
                if ($item['fraction_value'] && ($request->fraction != $item['fraction_value'])) {
                    return false;
                }
                if ($item['series_value'] && ($request->series != $item['series_value'])) {
                    return false;
                }
                return true;
            }
        });
        $result = collect([]);
        $currency = $raffle_draw->raffle->curr_code;
        $matches->filter(function ($item) use ($request, $matches, $result, $currency) {
            if ($matches->where('parent', '=', $item['order'])->where('math', '=', null)->isEmpty()) {
                $prize = ($item['fraction_value'] || $item['series_value']) ? $item['ticket_prize'] : $item['fraction_prize'];
                $result->push([
                    'prize' => $prize,
                    'prize_name' => '#' . $item['name'] . '#',
                    'currency' => $currency,
                ]);
            }
        });
        return $this->successResponse(['data' => $result]);
    }
}
