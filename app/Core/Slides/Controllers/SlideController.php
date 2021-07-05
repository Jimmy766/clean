<?php

namespace App\Core\Slides\Controllers;

use App\Core\Assets\Models\Asset;
use App\Core\Base\Classes\ModelConst;
use App\Core\Base\Services\TranslateArrayService;
use App\Core\Base\Traits\CacheUtilsTraits;
use App\Core\Slides\Requests\StoreSlideRequest;
use App\Core\Assets\Resources\AssetResource;
use App\Core\Languages\Resources\LanguageResource;
use App\Core\Countries\Resources\RegionRapiResource;
use App\Core\Terms\Models\Language;
use App\Core\Countries\Models\RegionRapi;
use App\Core\Slides\Services\AllSlidesAvailableService;
use App\Core\Slides\Services\DeleteConfigSlideService;
use App\Core\Slides\Services\DeleteImageSlideService;
use App\Core\Slides\Services\DeleteProgramSlideService;
use App\Core\Slides\Services\DeleteRegionSlideService;
use App\Core\Slides\Services\StoreConfigSlideService;
use App\Core\Slides\Services\StoreImageSlideService;
use App\Core\Slides\Services\StoreProgramSlideService;
use App\Core\Slides\Services\StoreRegionSlideService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Core\Base\Services\LogType;
use App\Core\Slides\Models\Slide;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DB;
use Exception;
use App\Core\Base\Traits\ApiResponser;
use App\Core\Slides\Collections\SlideCollection;
use App\Core\Slides\Resources\SlideResource;
use Swagger\Annotations as SWG;

/**
 * Class SlideController
 * @package App\Http\Controllers
 */
class SlideController extends Controller
{

    use ApiResponser;
    use CacheUtilsTraits;

    /**
     * @var StoreProgramSlideService
     */
    private $storeProgramSlideService;
    /**
     * @var StoreConfigSlideService
     */
    private $storeConfigSlideService;
    /**
     * @var DeleteConfigSlideService
     */
    private $deleteConfigSlideService;
    /**
     * @var DeleteProgramSlideService
     */
    private $deleteProgramSlideService;
    /**
     * @var \App\Core\Slides\Services\StoreRegionSlideService
     */
    private $storeRegionSlideService;
    /**
     * @var DeleteRegionSlideService
     */
    private $deleteRegionSlideService;

    /**
     * @var StoreImageSlideService
     */
    private $storeImageSlideService;
    /**
     * @var DeleteImageSlideService
     */
    private $deleteImageSlideService;
    /**
     * @var \App\Core\Slides\Services\AllSlidesAvailableService
     */
    private $allSlidesAvailableService;

    public function __construct(
        StoreProgramSlideService $storeProgramSlideService,
        StoreConfigSlideService $storeConfigSlideService,
        StoreRegionSlideService $storeRegionSlideService,
        DeleteConfigSlideService $deleteConfigSlideService,
        DeleteProgramSlideService $deleteProgramSlideService,
        DeleteRegionSlideService $deleteRegionSlideService,
        StoreImageSlideService $storeImageSlideService,
        DeleteImageSlideService $deleteImageSlideService,
        AllSlidesAvailableService $allSlidesAvailableService
    ) {
        $this->middleware('client.credentials');
        $this->middleware('check.external_access')->except(
            [ 'getSlidesAvailable' ]
        );
        $this->storeProgramSlideService  = $storeProgramSlideService;
        $this->storeConfigSlideService   = $storeConfigSlideService;
        $this->storeRegionSlideService   = $storeRegionSlideService;
        $this->deleteConfigSlideService  = $deleteConfigSlideService;
        $this->deleteProgramSlideService = $deleteProgramSlideService;
        $this->deleteRegionSlideService  = $deleteRegionSlideService;
        $this->storeImageSlideService    = $storeImageSlideService;
        $this->deleteImageSlideService   = $deleteImageSlideService;
        $this->allSlidesAvailableService = $allSlidesAvailableService;
    }

    /**
     * @SWG\Get(
     *   path="/slides",
     *   summary="Show slides list ",
     *   tags={"Slides"},
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
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/Slide")),
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
        $slides = Slide::query()->with([
            'regions.countriesRegions.country',
            'programSlide.datePrograms',
            'images.asset',
            'configSlide.language',
        ])->paginateByRequest();

        $slidesCollection = new SlideCollection($slides);

        $data[ 'slides' ] = $slidesCollection;

        return $this->successResponseWithMessage($data);
    }

    /**
     * @SWG\Get(
     *   path="/slides/available",
     *   summary="Get slide available by region/country/date",
     *   tags={"Slides"},
     *   consumes={"application/json"},
     *
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *
     *
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/Slide")),
     *     ),
     *   ),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function getSlidesAvailable(Request $request)
    {
        $dataSlideService = $this->allSlidesAvailableService->execute();
        $slides           = $dataSlideService[ 'slides' ];
        $data[ 'slides' ] = SlideResource::collection($slides);

        return $data;
    }

    /**
     * @SWG\Get(
     *   path="/slides/create",
     *   summary="Get info necessary to store Slide",
     *   tags={"Slides"},
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
     *
     */
    public function create(Request $request)
    {
        $regions   = RegionRapi::all();
        $regions   = RegionRapiResource::collection($regions);
        $languages = Language::all();
        $languages = LanguageResource::collection($languages);
        $assets    = Asset::all();
        $assets    = AssetResource::collection($assets);

        $data = [
            'assets'                 => $assets,
            'regions'                => $regions,
            'languages'              => $languages,
            'type_range_program'     => TranslateArrayService::execute(ModelConst::PROGRAM_TYPE_RANGE),
            'type_current_program'   => TranslateArrayService::execute
            (ModelConst::PROGRAM_TYPE_RECURRENT_RANGE),
            'period_current_program' => TranslateArrayService::execute(ModelConst::PROGRAM_PERIOD_RECURRENT),
        ];

        return $this->successResponseWithMessage($data);

    }

    /**
     * @param StoreSlideRequest $storeSlideRequest
     * @return JsonResponse
     */
    /**
     * @SWG\Post(
     *   path="/slides",
     *   summary="Store new slide with program, config and images ",
     *   tags={"Slides"},
     *   consumes={"application/json"},
     *
     *   @SWG\Parameter(
     *     name="request",
     *     in="body",
     *     description="request body json",
     *     type="object",
     *     @SWG\Schema(
     *         ref="#/definitions/StoreSlide",
     *     )
     *   ),
     *   security={
     *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *
     *
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="object", ref="#/definitions/Slide"),
     *     ),
     *   ),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function store(StoreSlideRequest $storeSlideRequest)
    {
        $successMessage = __('Successful registration');
        $errorMessage   = __('Unexpected error occurred while trying to process your request.');

        try {
            DB::beginTransaction();

            $slide = new Slide();
            $slide->fill($storeSlideRequest->validated());
            $slide->save();

            $this->storeProgramSlideService->execute($slide, $storeSlideRequest);
            $this->storeConfigSlideService->execute($slide, $storeSlideRequest);
            $this->storeImageSlideService->execute($slide, $storeSlideRequest);
            $this->storeRegionSlideService->execute($slide, $storeSlideRequest);

            DB::commit();

            $tag = [ Slide::TAG_CACHE_MODEL, ];
            $this->forgetCacheByTag($tag);

            $slide->load([
                'regions.countriesRegions.country',
                'programSlide.datePrograms',
                'images.asset',
                'configSlide.language',
            ]);

            $data[ 'slide' ] = new SlideResource($slide);

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
     *   path="/slides/{id_slide}",
     *   summary="get slide by id_slide",
     *   tags={"Slides"},
     *   consumes={"application/json"},
     *   @SWG\Parameter(
     *     name="id_slide",
     *     in="path",
     *     description="Slide ID",
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
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/Slide")),
     *     ),
     *   ),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     * @param Slide $slide
     * @return JsonResponse
     */
    public function show(Slide $slide)
    {
        $slide->load([
            'regions.countriesRegions.country',
            'programSlide.datePrograms',
            'images.asset',
            'configSlide.language',
        ]);

        $data[ 'slide' ] =new SlideResource($slide);
        return $this->successResponseWithMessage($data);
    }

    /**
     * @SWG\Put(
     *   path="/slides/{id_slide}",
     *   summary="Update new slide with program, config and images ",
     *   tags={"Slides"},
     *   consumes={"application/json"},
     *   @SWG\Parameter(
     *     name="id_slide",
     *     in="path",
     *     description="Slide ID",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="request",
     *     in="body",
     *     description="request body json",
     *     type="object",
     *     @SWG\Schema(
     *         ref="#/definitions/StoreSlide",
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
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/Slide")),
     *     ),
     *   ),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function update(Slide $slide, StoreSlideRequest $storeSlideRequest)
    {
        $successMessage = __('Successful update');
        $errorMessage   = __('Unexpected error occurred while trying to process your request.');

        try {
            DB::beginTransaction();

            $slide->fill($storeSlideRequest->validated());
            $slide->save();

            $this->deleteConfigSlideService->execute($slide);
            $this->deleteProgramSlideService->execute($slide);
            $this->deleteRegionSlideService->execute($slide);
            $this->deleteImageSlideService->execute($slide);
            $this->storeProgramSlideService->execute($slide, $storeSlideRequest);
            $this->storeConfigSlideService->execute($slide, $storeSlideRequest);
            $this->storeImageSlideService->execute($slide, $storeSlideRequest);
            $this->storeRegionSlideService->execute($slide, $storeSlideRequest);

            DB::commit();

            $tag = [ Slide::TAG_CACHE_MODEL, ];
            $this->forgetCacheByTag($tag);

            $slide->load([
                'regions.countriesRegions.country',
                'programSlide.datePrograms',
                'images.asset',
                'configSlide.language',
            ]);

            $data[ 'slide' ] = new SlideResource($slide);
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
     *   path="/slides/{id_slide}",
     *   summary="Delete slide by id_slide",
     *   tags={"Slides"},
     *   consumes={"application/json"},
     *   @SWG\Parameter(
     *     name="id_slide",
     *     in="path",
     *     description="Slide ID",
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
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/Slide")),
     *     ),
     *   ),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     * @param Slide $slide
     * @return JsonResponse
     */
    public function destroy(Slide $slide)
    {
        $successMessage = __('Successful delete');
        $errorMessage   = __('Unexpected error occurred while trying to process your request.');

        try {
            DB::beginTransaction();

            $this->deleteConfigSlideService->execute($slide);
            $this->deleteProgramSlideService->execute($slide);
            $this->deleteRegionSlideService->execute($slide);
            $slide->delete();
            DB::commit();

            $tag = [ Slide::TAG_CACHE_MODEL, ];
            $this->forgetCacheByTag($tag);

            $data[ 'region' ] = new SlideResource($slide);

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
