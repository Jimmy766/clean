<?php

namespace App\Core\Banners\Controllers;


use App\Core\Banners\Models\Banner;
use App\Core\Base\Services\TranslateArrayService;
use App\Core\Base\Traits\CacheUtilsTraits;
use App\Core\Banners\Collections\BannerCollection;
use App\Core\Banners\Requests\StoreBannerRequest;
use App\Core\Banners\Resources\BannerResource;
use App\Core\Languages\Resources\LanguageResource;
use App\Core\Countries\Resources\RegionRapiResource;
use App\Core\Terms\Models\Language;
use App\Http\Controllers\Controller;
use App\Core\Clients\Services\IP2LocTrillonario;
use App\Core\Banners\Models\RegionBannerPivot;
use App\Core\Countries\Models\RegionRapi;
use App\Core\Banners\Services\DeleteConfigBannerService;
use App\Core\Banners\Services\DeleteRegionBannerService;
use App\Core\Banners\Services\GetBannerAvailableService;
use App\Core\Banners\Services\StoreConfigBannerService;
use App\Core\Banners\Services\StoreRegionBannerService;
use App\Core\Base\Services\LogType;
use App\Core\Base\Traits\ApiResponser;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use DB;
use Swagger\Annotations as SWG;

class BannerController extends Controller
{

    use ApiResponser;
    use CacheUtilsTraits;

    /**
     * @var StoreRegionBannerService
     */
    private $storeRegionBannerService;
    /**
     * @var \App\Core\Banners\Services\DeleteRegionBannerService
     */
    private $deleteRegionBannerService;
    /**
     * @var \App\Core\Banners\Services\StoreConfigBannerService
     */
    private $storeConfigBannerService;
    /**
     * @var DeleteConfigBannerService
     */
    private $deleteConfigBannerService;
    /**
     * @var \App\Core\Banners\Services\GetBannerAvailableService
     */
    private $getBannerAvailableService;

    public function __construct(
        StoreRegionBannerService $storeRegionBannerService,
        DeleteRegionBannerService $deleteRegionBannerService,
        StoreConfigBannerService $storeConfigBannerService,
        DeleteConfigBannerService $deleteConfigBannerService,
        GetBannerAvailableService $getBannerAvailableService
        )
    {
        $this->middleware('client.credentials');
        $this->middleware('check.external_access')->except(
            [ 'getBannerAvailable' ]
        );
        $this->storeRegionBannerService= $storeRegionBannerService;
        $this->deleteRegionBannerService= $deleteRegionBannerService;
        $this->storeConfigBannerService= $storeConfigBannerService;
        $this->deleteConfigBannerService= $deleteConfigBannerService;
        $this->getBannerAvailableService = $getBannerAvailableService;
    }

    /**
     * @SWG\Get(
     *   path="/banners",
     *   summary="Show banners list ",
     *   tags={"Banner"},
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
     *     {"Key-access": {},"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/Banner")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(
     *     response="403",
     *     description="Forbidden Access",
     *     @SWG\Schema(
     *       @SWG\Property(property="error", type="string", description="Message error",
     *                                       example="This data is not allowed for you"),
     *       @SWG\Property(property="code", type="integer", description="Response code",
     *                                      example="403"),
     *     ),
     *   ),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     * @return JsonResponse
     */
    public function index()
    {

        $relation = [ 'regions.countriesRegions.country', 'configBanner.languages' ];
        $banner=Banner::query()->with($relation)->paginateByRequest();
        $bannersCollection=new BannerCollection($banner);
        $data['banners']=$bannersCollection;

        return $this->successResponseWithMessage($data );
    }

    /**
     * @SWG\Get(
     *   path="/banners/available",
     *   summary="Get banner available by region/country",
     *   tags={"Banner"},
     *   consumes={"application/json"},
     *
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *
     *   @SWG\Parameter(
     *     name="type_product",
     *     in="query",
     *     description="type_product",
     *     type="integer",
     *   ),
     *
     *   @SWG\Parameter(
     *     name="type",
     *     in="query",
     *     description="type",
     *     type="integer",
     *   ),
     *
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items
     * (ref="#/definitions/Banner")),
     *     ),
     *   ),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function getBannerAvailable(Request $request)
    {
        [$codeCountry] = IP2LocTrillonario::get_iso('');
        $banner = $this->getBannerAvailableService->execute($codeCountry, $request);
        $bannersResource = null;
        if ($banner !== null) {
            $bannersResource = new BannerResource($banner);
        }
        $data[ 'banner' ] = $bannersResource;

        return $this->successResponseWithMessage($data );
    }

    /**
     * @SWG\Get(
     *   path="/banners/create",
     *   summary="Get info necessary to store banners",
     *   tags={"Banner"},
     *   consumes={"application/json"},
     *
     *   security={
     *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *
     *
     *   @SWG\Response(response=200, ref="#/responses/200"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $regions     = RegionRapi::all();
        $regions     = RegionRapiResource::collection($regions);
        $languages   = Language::all();
        $languages   = LanguageResource::collection($languages);
        $typeProduct = TranslateArrayService::execute(Banner::BANNER_TYPE_PRODUCT);
        $type        = TranslateArrayService::execute(Banner::BANNER_TYPE);

        $data = [
            'regions'      => $regions,
            'languages'    => $languages,
            'type_product' => $typeProduct,
            'type'         => $type,
        ];

        return $this->successResponseWithMessage($data);
    }


    /**
     * @SWG\Post(
     *   path="/banners",
     *   summary="Store new banner with regions and config ",
     *   tags={"Banner"},
     *   consumes={"application/json"},
     *
     *   @SWG\Parameter(
     *     name="request",
     *     in="body",
     *     description="request body json",
     *     type="object",
     *     @SWG\Schema(
     *         ref="#/definitions/StoreBannerRequest",
     *     )
     *   ),
     *
     *   security={
     *     {"Key-access": {},"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/Banner")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(
     *     response="403",
     *     description="Forbidden Access",
     *     @SWG\Schema(
     *       @SWG\Property(property="error", type="string", description="Message error",
     *                                       example="This data is not allowed for you"),
     *       @SWG\Property(property="code", type="integer", description="Response code",
     *                                      example="403"),
     *     ),
     *   ),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function store(StoreBannerRequest $storeBannerRequest)
    {
        $errorMessage   = __('Unexpected error occurred while trying to process your request.');
        $successMessage = __('Successful registration');

        try {
            DB::beginTransaction();


            $banner =new  Banner();
            $banner->fill($storeBannerRequest->validated());
            $banner->save();

            $this->storeRegionBannerService->execute($banner,$storeBannerRequest);
            $this->storeConfigBannerService->execute($banner,$storeBannerRequest);
            DB::commit();

            $tag = [ Banner::TAG_CACHE_MODEL, ];
            $this->forgetCacheByTag($tag);

            $banner->load([ 'regions.countriesRegions.country', 'configBanner.languages' ]);

            $data['banner']=new BannerResource($banner);
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
     *   path="/banners/{id_banner}",
     *   summary="Show specific banner ",
     *   tags={"Banner"},
     *   @SWG\Parameter(
     *     name="id_banner",
     *     in="path",
     *     description="Banner ID",
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
     *         @SWG\Property(property="data", type="array",
     *                                        @SWG\Items(ref="#/definitions/Banner")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(
     *     response="403",
     *     description="Forbidden Access",
     *     @SWG\Schema(
     *       @SWG\Property(property="error", type="string", description="Message error",
     *                                       example="This data is not allowed for you"),
     *       @SWG\Property(property="code", type="integer", description="Response code",
     *                                      example="403"),
     *     ),
     *   ),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     * @param Banner $banner
     * @return JsonResponse
     */
    public function show(Banner $banner)
    {
        $banner->load([ 'regions.countriesRegions.country', 'configBanner.languages' ]);
        $data['banner']=new BannerResource($banner);

        return $this->successResponseWithMessage($data);
    }

    /**
     * @SWG\Put(
     *   path="/banners/{id_banner}",
     *   summary="Update record on banners ",
     *   tags={"Banner"},
     *   consumes={"application/json"},
     *
     *   @SWG\Parameter(
     *     name="id_banner",
     *     in="path",
     *     description="Banner ID",
     *     required=true,
     *     type="string"
     *   ),
     *
     *   @SWG\Parameter(
     *     name="request",
     *     in="body",
     *     description="request body json",
     *     type="object",
     *     @SWG\Schema(
     *         ref="#/definitions/StoreBannerRequest",
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
     *         @SWG\Property(property="data", type="array",
     *                                        @SWG\Items(ref="#/definitions/Banner")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(
     *     response="403",
     *     description="Forbidden Access",
     *     @SWG\Schema(
     *       @SWG\Property(property="error", type="string", description="Message error",
     *                                       example="This data is not allowed for you"),
     *       @SWG\Property(property="code", type="integer", description="Response code",
     *                                      example="403"),
     *     ),
     *   ),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     * @param \App\Core\Banners\Requests\StoreBannerRequest $request
     * @param Banner                                        $banner
     * @return JsonResponse
     */
    public function update(Banner $banner, StoreBannerRequest $storeBannerRequest)
    {
        $errorMessage   = __('Unexpected error occurred while trying to process your request.');
        $successMessage = __('Successful update');
        try {
            DB::beginTransaction();

            $banner->fill($storeBannerRequest->validated());
            $banner->save();

            $this->deleteRegionBannerService->execute($banner);
            $this->deleteConfigBannerService->execute($banner);
            $this->storeRegionBannerService->execute($banner,$storeBannerRequest);
            $this->storeConfigBannerService->execute($banner,$storeBannerRequest);
            DB::commit();

            $tag = [ Banner::TAG_CACHE_MODEL, ];
            $this->forgetCacheByTag($tag);

            $banner->load([ 'regions.countriesRegions.country', 'configBanner.languages' ]);
            $data['banner']=new BannerResource($banner);
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
     *   path="/banners/{id_banner}",
     *   summary="Deleted specific banner ",
     *   tags={"Banner"},
     *   @SWG\Parameter(
     *     name="id_banner",
     *     in="path",
     *     description="Banner ID",
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
     *         @SWG\Property(property="data", type="array",
     *                                        @SWG\Items(ref="#/definitions/Banner")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(
     *     response="403",
     *     description="Forbidden Access",
     *     @SWG\Schema(
     *       @SWG\Property(property="error", type="string", description="Message error",
     *                                       example="This data is not allowed for you"),
     *       @SWG\Property(property="code", type="integer", description="Response code",
     *                                      example="403"),
     *     ),
     *   ),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function destroy(Banner $banner)
    {
        $errorMessage   = __('Unexpected error occurred while trying to process your request.');
        $successMessage = __('Successful delete');
        try {
            DB::beginTransaction();

            $banner->delete();

            $this->deleteRegionBannerService->execute($banner);
            $this->deleteConfigBannerService->execute($banner);
            DB::commit();

            $tag = [ Banner::TAG_CACHE_MODEL, ];
            $this->forgetCacheByTag($tag);

            $data['banner']=new BannerResource($banner);
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
