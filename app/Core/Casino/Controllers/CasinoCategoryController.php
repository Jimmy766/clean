<?php

namespace App\Core\Casino\Controllers;

use App\Core\Casino\Models\CasinoCategory;
use App\Core\Base\Classes\ModelConst;
use App\Core\Casino\Collections\CasinoGameCollection;
use App\Core\Casino\Requests\CasinoCategoryRequest;
use App\Core\Casino\Resources\CasinoCategoryResource;
use App\Core\Countries\Services\CheckCountryAndStateBlocksService;
use App\Core\Casino\Services\GetCasinoGamesPaginateService;
use App\Core\Casino\Services\GetCasinoWithProviderService;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;

class CasinoCategoryController extends ApiController
{
    /**
     * CasinoCategoryController constructor.
     */
    private $getCasinoWithProviderService;
    private $getCasinoGamesPaginateService;
    public function __construct(
        GetCasinoWithProviderService $getCasinoWithProviderService,
        GetCasinoGamesPaginateService $getCasinoGamesAndGetUrlService
        ){
        parent::__construct();
        $this->getCasinoWithProviderService=$getCasinoWithProviderService;
        $this->getCasinoGamesPaginateService=$getCasinoGamesAndGetUrlService;
        $this->middleware('client.credentials');
    }

    /**
     * @SWG\Get(
     *   path="/games",
     *   tags={"Games"},
     *   summary="Show games list by category",
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Parameter(
     *     name="live",
     *     in="query",
     *     description="Live (0=Casino ,1=Live Casino)",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="provider",
     *     in="query",
     *     description="Provider (1=MultiSlot, 2=Oryx, 3=RedTiger)",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/CasinoCategory")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(CasinoCategoryRequest $request) {

        $provider = $request->provider;
        $live= $request->live;
        $casino = $this->getCasinoWithProviderService->execute($live,$provider);
        return $this->showAllNoPaginated($casino);
    }

    /**
     * @SWG\Get(
     *   path="/games/list",
     *   tags={"Games"},
     *   summary="Show games list by category",
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Parameter(
     *     name="page_pagination",
     *     in="query",
     *     description="page pagination",
     *     type="integer",
     *     default=1
     *   ),
     *   @SWG\Parameter(
     *     name="size_pagination",
     *     in="query",
     *     description="size pagination",
     *     type="integer",
     *     default=15
     *   ),
     *   @SWG\Parameter(
     *     name="live",
     *     in="query",
     *     description="Live (0=Casino ,1=Live Casino)",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="name",
     *     in="query",
     *     description="Game Name",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="popular",
     *     in="query",
     *     description="Popular (0=False ,1=True)",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="provider",
     *     in="query",
     *     description="Provider (1=MultiSlot, 2=Oryx, 3=RedTiger)",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="category",
     *     in="query",
     *     description="Category ID",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/CasinoGameResponse")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function gamesPaginate(Request $request)
    {
        $casino=$this->getCasinoGamesPaginateService->execute($request);

        $listExcept = ModelConst::EXCEPT_COUNTRY_REGION_CASINO_SPORT_SCRATCH;
        $exceptCountryState = collect($listExcept);

        $casino = CheckCountryAndStateBlocksService::execute($exceptCountryState, $casino, 1);
        $data['casino'] = new CasinoGameCollection($casino);

        $categories = CasinoCategory::query()->getFromCache();

        $data[ 'categories' ] = CasinoCategoryResource::collection($categories);

        return $this->successResponseWithMessage($data);
    }
}
