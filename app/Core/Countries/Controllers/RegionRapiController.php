<?php

namespace App\Core\Countries\Controllers;

use App\Core\Base\Traits\CacheUtilsTraits;
use App\Core\Countries\Collections\RegionRapiCollection;
use App\Core\Countries\Resources\RegionRapiResource;
use App\Core\Countries\Models\RegionRapi;
use App\Core\Countries\Services\DeleteCountriesOfRegionService;
use App\Core\Countries\Services\FormatCountriesToCreateRegionService;
use App\Core\Countries\Services\GetCountriesService;
use App\Core\Slides\Services\StoreCountriesOfRegionService;
use App\Core\Base\Traits\ApiResponser;
use App\Core\Base\Services\LogType;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use DB;
use App\Core\Countries\Requests\StoreRegionRapiRequest;

class RegionRapiController extends Controller
{
    use ApiResponser;
    use CacheUtilsTraits;

    /**
     * @var StoreCountriesOfRegionService
     */
    private $storeCountriesOfRegionService;
    /**
     * @var \App\Core\Countries\Services\DeleteCountriesOfRegionService
     */
    private $deleteCountriesOfRegionService;
    /**
     * @var \App\Core\Countries\Services\GetCountriesService
     */
    private $getCountriesService;
    /**
     * @var FormatCountriesToCreateRegionService
     */
    private $formatCountriesToCreateRegionService;

    public function __construct(
        StoreCountriesOfRegionService $storeCountriesOfRegionService,
        DeleteCountriesOfRegionService $deleteCountriesOfRegionService,
        GetCountriesService $getCountriesService,
        FormatCountriesToCreateRegionService $formatCountriesToCreateRegionService
    ) {
        $this->middleware('client.credentials');
        $this->middleware('check.external_access');
        $this->storeCountriesOfRegionService = $storeCountriesOfRegionService;
        $this->deleteCountriesOfRegionService = $deleteCountriesOfRegionService;
        $this->getCountriesService = $getCountriesService;
        $this->formatCountriesToCreateRegionService = $formatCountriesToCreateRegionService;
    }


    /**
     * @SWG\Get(
     *   path="/region_rapi",
     *   summary="Show regions list ",
     *   tags={"Region Rapi"},
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
     *   security={
     *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/RegionRapi")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(
     *     response="403",
     *     description="Forbidden Access",
     *     @SWG\Schema(
     *       @SWG\Property(property="error", type="string", description="Message error", example="This data is not allowed for you"),
     *       @SWG\Property(property="code", type="integer", description="Response code", example="403"),
     *     ),
     *   ),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function index(Request $request)
    {
        $regions = RegionRapi::with([ 'countriesRegions.country' ])->paginateByRequest();

        $regionsCollection = new RegionRapiCollection($regions);

        $data['regions'] = $regionsCollection;

        return $this->successResponseWithMessage($data);
    }

    public function create()
    {
        $countries = $this->getCountriesService->execute();
        $countries = $this->formatCountriesToCreateRegionService->execute($countries);

        $data['countries'] = $countries;
        return $this->successResponseWithMessage($data);


    }

    /**
     * Public method to save a new region in RAPI
     *
     * @param   StoreRegionRapiRequest $request
     * @return  JsonResponse
     */
    /**
     * @SWG\Post(
     *   path="/region_rapi",
     *   summary="Create a new region record in RAPI",
     *   tags={"Region Rapi"},
     *   consumes={"application/json"},
     *
     *   @SWG\Parameter(
     *     name="request",
     *     in="body",
     *     description="Request body in JSON format",
     *     type="object",
     *     @SWG\Schema(
     *         ref="#/definitions/StoreRegionRapi",
     *     )
     *   ),
     *   security={
     *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/RegionRapi")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function store(StoreRegionRapiRequest $request)
    {
        $successMessage = __('Operation Successful');
        $errorMessage   = __('An error has occurred');

        try {
            DB::beginTransaction();

            $regionRapi = new RegionRapi();
            $regionRapi->fill($request->validated());
            $regionRapi->save();
            $this->storeCountriesOfRegionService->execute($regionRapi, $request);
            DB::commit();

            $tag = [ RegionRapi::TAG_CACHE_MODEL, ];
            $this->forgetCacheByTag($tag);

            $regionRapi->load([ 'countriesRegions.country' ]);

            $data['region'] = new RegionRapiResource($regionRapi);

            return $this->successResponseWithMessage($data, $successMessage, Response::HTTP_CREATED);

        }
        catch(Exception $exception) {
            DB::rollBack();

            LogType::error(__FILE__, __LINE__, $errorMessage, [
                'exception' => $exception,
                'usersId'   => Auth::id(),
            ]);
            return $this->errorCatchResponse($exception, $errorMessage, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * @SWG\Get(
     *   path="/region_rapi/{region_rapi_id}",
     *   summary="Show specific region rapi ",
     *   tags={"Region Rapi"},
     *   @SWG\Parameter(
     *     name="region_rapi_id",
     *     in="path",
     *     description="Region Rapi ID",
     *     required=true,
     *     type="string"
     *   ),
     *   security={
     *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/RegionRapi")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(
     *     response="403",
     *     description="Forbidden Access",
     *     @SWG\Schema(
     *       @SWG\Property(property="error", type="string", description="Message error", example="This data is not allowed for you"),
     *       @SWG\Property(property="code", type="integer", description="Response code", example="403"),
     *     ),
     *   ),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     * @param \App\Core\Countries\Models\RegionRapi $regionRapi
     * @return JsonResponse
     */
    public function show(RegionRapi $regionRapi)
    {
        $regionRapi->load([ 'countriesRegions.country' ]);

        $data['region'] = new RegionRapiResource($regionRapi);

        return $this->successResponseWithMessage($data);
    }


    /**
     * @SWG\Put(
     *   path="/region_rapi/{region_rapi_id}",
     *   summary="Update a record for a region in RAPI",
     *   tags={"Region Rapi"},
     *   consumes={"application/json"},
     *   @SWG\Parameter(
     *     name="region_rapi_id",
     *     in="path",
     *     description="Region Rapi ID",
     *     required=true,
     *     type="string"
     *   ),
     *
     *   @SWG\Parameter(
     *     name="request",
     *     in="body",
     *     description="Request body in JSON format",
     *     type="object",
     *     @SWG\Schema(
     *         ref="#/definitions/StoreRegionRapi",
     *     )
     *   ),
     *
     *   security={
     *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/RegionRapi")),
     *     ),
     *   ),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     * @param \App\Core\Countries\Requests\StoreRegionRapiRequest $request
     * @param RegionRapi                                          $regionRapi
     * @return JsonResponse
     */
    public function update(RegionRapi $regionRapi, StoreRegionRapiRequest $request)
    {
        $successMessage = __('Successful update');
        $errorMessage   = __('An error has occurred');

        try {
            DB::beginTransaction();

            $regionRapi->fill($request->validated());
            $regionRapi->save();
            $this->deleteCountriesOfRegionService->execute($regionRapi);
            $this->storeCountriesOfRegionService->execute($regionRapi, $request);
            DB::commit();

            $tag = [ RegionRapi::TAG_CACHE_MODEL, ];
            $this->forgetCacheByTag($tag);

            $regionRapi->load([ 'countriesRegions.country' ]);

            $data['region'] = new RegionRapiResource($regionRapi);

            return $this->successResponseWithMessage($data, $successMessage);

        }
        catch(Exception $exception) {
            DB::rollBack();

            LogType::error(__FILE__, __LINE__, $errorMessage, [
                'exception' => $exception,
                'usersId'   => Auth::id(),
            ]);
            return $this->errorCatchResponse($exception, $errorMessage, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * @SWG\Delete(
     *   path="/region_rapi/{region_rapi_id}",
     *   summary="Deleted specific region rapi ",
     *   tags={"Region Rapi"},
     *   @SWG\Parameter(
     *     name="region_rapi_id",
     *     in="path",
     *     description="Region Rapi ID",
     *     required=true,
     *     type="string"
     *   ),
     *   security={
     *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/RegionRapi")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(
     *     response="403",
     *     description="Forbidden Access",
     *     @SWG\Schema(
     *       @SWG\Property(property="error", type="string", description="Message error", example="This data is not allowed for you"),
     *       @SWG\Property(property="code", type="integer", description="Response code", example="403"),
     *     ),
     *   ),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function destroy(RegionRapi $regionRapi)
    {
        $successMessage = __('Operation Successful');
        $errorMessage   = __('An error has occurred');

        try {
            DB::beginTransaction();

            $this->deleteCountriesOfRegionService->execute($regionRapi);
            $regionRapi->delete();
            DB::commit();

            $tag = [ RegionRapi::TAG_CACHE_MODEL, ];
            $this->forgetCacheByTag($tag);

            $data['region'] = new RegionRapiResource($regionRapi);

            return $this->successResponseWithMessage($data, $successMessage);

        }
        catch(Exception $exception) {
            DB::rollBack();

            LogType::error(__FILE__, __LINE__, $errorMessage, [
                'exception' => $exception,
                'usersId'   => Auth::id(),
            ]);
            return $this->errorCatchResponse($exception, $errorMessage, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
