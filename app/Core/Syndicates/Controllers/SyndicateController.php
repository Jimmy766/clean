<?php

namespace App\Core\Syndicates\Controllers;

use App\Core\Syndicates\Services\AllSyndicatesActiveService;
use App\Core\Syndicates\Models\Syndicate;
use App\Core\Base\Traits\Pixels;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Response;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class SyndicateController extends ApiController
{

    use Pixels;

    /**
     * @var \App\Core\Syndicates\Services\AllSyndicatesActiveService
     */
    private $allSyndicatesActiveService;

    public function __construct(AllSyndicatesActiveService $allSyndicatesActiveService)
    {
        parent::__construct();
        $this->middleware('auth:api')->except('show', 'index', 'lotteries', 'prices');
        $this->middleware('client.credentials')->only('index', 'show', 'lotteries', 'prices');
        $this->allSyndicatesActiveService = $allSyndicatesActiveService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    /**
     * @SWG\Get(
     *   path="/syndicates",
     *   summary="Show syndicates list ",
     *   tags={"Syndicates"},
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/Syndicate")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function index()
    {
        $syndicates = $this->allSyndicatesActiveService->execute();

        return $this->showAllNoPaginated($syndicates);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Core\Syndicates\Models\Syndicate $syndicate
     * @return Response
     */
    /**
     * @SWG\Get(
     *   path="/syndicates/{syndicate}",
     *   summary="Show syndicate details ",
     *   tags={"Syndicates"},
     *   @SWG\Parameter(
     *     name="syndicate",
     *     in="path",
     *     description="Syndicate Id.",
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
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/Syndicate")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function show($idSyndicate)
    {
        $relations = [
            'syndicate_lotteries.lottery.draws',
            'syndicate_prices.syndicate_price_lines',
            'syndicate_prices.lottery_time_draws',
            'syndicate_prices.syndicate.syndicate_lotteries',
            'routingFriendly',
        ];
        $syndicate = Syndicate::query()
            ->where('id', $idSyndicate)
            ->with($relations)
            ->firstFromCache();
        if($syndicate === null){
            throw new UnprocessableEntityHttpException(__('syndicate dont exist'), null,
                Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $prices = $syndicate->syndicate_prices->sortByDesc('draws')->values();
        $price = $prices->first();
        $price_id = $price->prc_id;
        $price_line_id = $price->syndicate_price_line['identifier'];
        $product = [
            'id' => $syndicate->id,
        ];
        $request = request();
        $request->merge(['pixel' => $this->retargeting(2, $product, $price_id, $price_line_id)]);

        return self::client_syndicates(1)->pluck('product_id')
            ->contains($syndicate->id) ? $this->showOne($syndicate) : $this->errorResponse(trans('lang.syndicate_forbidden'), 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Core\Syndicates\Models\Syndicate $syndicate
     * @return Response
     */
    /**
     * @SWG\Get(
     *   path="/syndicates/lotteries/{syndicate}",
     *   summary="Show syndicate lotteries ",
     *   tags={"Syndicates"},
     *   @SWG\Parameter(
     *     name="syndicate",
     *     in="path",
     *     description="Syndicate Id.",
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
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/LotterySyndicate")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function lotteries(Syndicate $syndicate)
    {
        return self::client_syndicates(1)->pluck('product_id')
            ->contains($syndicate->id) ? $this->successResponse(['data' => $syndicate->lotteries]) : $this->errorResponse(trans('lang.syndicate_forbidden'), 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Core\Syndicates\Models\Syndicate $syndicate
     * @return Response
     */
    /**
     * @SWG\Get(
     *   path="/syndicates/prices/{syndicate}",
     *   summary="Show syndicate prices",
     *   tags={"Syndicates"},
     *   @SWG\Parameter(
     *     name="syndicate",
     *     in="path",
     *     description="Syndicate Id.",
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
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/SyndicatePrice")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function prices(Syndicate $syndicate)
    {
        return self::client_syndicates(1)->pluck('product_id')
            ->contains($syndicate->id) ? $this->successResponse(['data' => $syndicate->prices_list]) : $this->errorResponse(trans('lang.syndicate_forbidden'), 403);
    }
}
