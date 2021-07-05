<?php

/** @noinspection PhpUnused */
/** @noinspection PhpUnusedParameterInspection */

namespace App\Core\Skins\Controllers;

use App\Core\Base\Classes\ModelConst;
use App\Core\Base\Services\TranslateArrayService;
use App\Core\Base\Traits\CacheUtilsTraits;
use App\Core\Languages\Resources\LanguageResource;
use App\Core\Countries\Resources\RegionRapiResource;
use App\Core\Terms\Models\Language;
use App\Core\Countries\Models\RegionRapi;
use App\Core\Skins\Services\AllSkinsAvailableService;
use App\Core\Skins\Services\DeleteConfigSkinService;
use App\Core\Skins\Services\DeleteProgramSkinService;
use App\Core\Skins\Services\DeleteRegionSkinService;
use App\Core\Skins\Services\StoreConfigSkinService;
use App\Core\Skins\Services\StoreProgramSkinService;
use App\Core\Skins\Services\StoreRegionSkinService;
use App\Http\Controllers\Controller;
use Exception;
use App\Core\Skins\Models\Skin;
use App\Core\Base\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Core\Base\Services\LogType;
use Illuminate\Http\JsonResponse;
use DB;
use Illuminate\Support\Facades\Auth;
use App\Core\Skins\Resources\SkinResource;
use App\Core\Skins\Requests\StoreSkinRequest;
use App\Core\Skins\Collections\SkinCollection;
use Swagger\Annotations as SWG;

/**
 * Class SkinController
 * @package App\Http\Controllers
 */
class SkinController extends Controller
{
    use ApiResponser;
    use CacheUtilsTraits;

    /**
     * @var StoreProgramSkinService
     */
    private $storeProgramSkinService;
    /**
     * @var StoreConfigSkinService
     */
    private $storeConfigSkinService;
    /**
     * @var StoreRegionSkinService
     */
    private $storeRegionSkinService;
    /**
     * @var DeleteConfigSkinService
     */
    private $deleteConfigSkinService;
    /**
     * @var DeleteProgramSkinService
     */
    private $deleteProgramSkinService;
    /**
     * @var DeleteRegionSkinService
     */
    private $deleteRegionSkinService;
    /**
     * @var AllSkinsAvailableService
     */
    private $allSkinsAvailableService;

    public function __construct(
        StoreProgramSkinService $storeProgramSkinService,
        StoreConfigSkinService $storeConfigSkinService,
        StoreRegionSkinService $storeRegionSkinService,
        DeleteConfigSkinService $deleteConfigSkinService,
        DeleteProgramSkinService $deleteProgramSkinService,
        DeleteRegionSkinService $deleteRegionSkinService,
        AllSkinsAvailableService $allSkinsAvailableService
    ) {
        $this->middleware('client.credentials');
        $this->middleware('check.external_access')->except(
            [ 'getSkinsAvailable' ]
        );
        $this->storeProgramSkinService   = $storeProgramSkinService;
        $this->storeConfigSkinService    = $storeConfigSkinService;
        $this->storeRegionSkinService    = $storeRegionSkinService;
        $this->deleteConfigSkinService   = $deleteConfigSkinService;
        $this->deleteProgramSkinService  = $deleteProgramSkinService;
        $this->deleteRegionSkinService   = $deleteRegionSkinService;
        $this->allSkinsAvailableService = $allSkinsAvailableService;
    }

    /**
     * @SWG\Get(
     *   path="/skins",
     *   summary="Show skin list ",
     *   tags={"Skin"},
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
     *         @SWG\Property(property="data", type="array",
     *                                        @SWG\Items(ref="#/definitions/Skin")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(
     *     response="403",
     *     description="Forbidden Access",
     *     @SWG\Schema(
     *       @SWG\Property(property="error", type="string",
     *                                       description="Message error",
     *                                       example="This data is not allowed
     *                                       for you"),
     *       @SWG\Property(property="code", type="integer",
     *                                      description="Response code",
     *                                      example="403"),
     *     ),
     *   ),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $relations = [
            'regions.countriesRegions.country',
            'programSkin.datePrograms',
            'configSkin.files',
            'configSkin.texts',
            'configSkin.languages',
        ];

        $skins = Skin::query()->with($relations)->paginateByRequest();

        $skinsCollection = new SkinCollection($skins);

        $data[ 'skins' ] = $skinsCollection;

        return $this->successResponseWithMessage($data);
    }

    /**
     * @SWG\Get(
     *   path="/skins/create",
     *   summary="Get info necessary to store Skins",
     *   tags={"Skin"},
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
        $regions   = RegionRapi::all();
        $regions   = RegionRapiResource::collection($regions);
        $languages = Language::all();
        $languages = LanguageResource::collection($languages);

        $data = [
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
     * @SWG\Get(
     *   path="/skins/available",
     *   summary="Get skin available by region/country/date",
     *   tags={"Skin"},
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
     *         @SWG\Property(property="data", type="array", @SWG\Items
     * (ref="#/definitions/Skin")),
     *     ),
     *   ),
     * @SWG\Response(response=422, ref="#/responses/422"),
     * @SWG\Response(response=401, ref="#/responses/401"),
     * @SWG\Response(response=403, ref="#/responses/403"),
     * @SWG\Response(response=500, ref="#/responses/500"),
     * )
     * @param Request $request
     * @return array
     */
    public function getSkinsAvailable(Request $request): array
    {
        $skins           = $this->allSkinsAvailableService->execute();
        //TODO talk with frontend to use standard from collection/resource
        $data[ 'skins' ] = $skins;
        return $data;
    }

    /**
     * @SWG\Post(
     *   path="/skins",
     *   summary="Store new record on skins ",
     *   tags={"Skin"},
     *   consumes={"application/json"},
     *   @SWG\Parameter(
     *     name="request",
     *     in="body",
     *     description="Request body in JSON format",
     *     type="object",
     *     @SWG\Schema(
     *         ref="#/definitions/StoreSkin",
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
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/Skin")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     * @param \App\Core\Skins\Requests\StoreSkinRequest $request
     * @return JsonResponse|null
     */
    public function store(StoreSkinRequest $request): ?JsonResponse
    {
        $successMessage = __('Operation Successful');
        $errorMessage   = __('An error has occurred');

        try {
            DB::beginTransaction();

            $skin = new Skin();
            $skin->fill($request->validated());
            $skin->save();
            $this->storeProgramSkinService->execute($skin, $request);
            $this->storeConfigSkinService->execute($skin, $request);
            $this->storeRegionSkinService->execute($skin, $request);
            DB::commit();

            $tag = [ Skin::TAG_CACHE_MODEL, ];
            $this->forgetCacheByTag($tag);

            $skin->load(
                [
                    'regions.countriesRegions.country',
                    'programSkin.datePrograms',
                    'configSkin.files',
                    'configSkin.texts',
                    'configSkin.languages',
                ]
            );

            $data[ 'skin' ] = new SkinResource($skin);

            return $this->successResponseWithMessage(
                $data,
                $successMessage,
                Response::HTTP_CREATED
            );

        }
        catch(Exception $exception) {
            DB::rollBack();

            LogType::error(
                __FILE__,
                __LINE__,
                $errorMessage,
                [
                    'exception' => $exception,
                    'usersId'   => Auth::id(),
                ]
            );
            return $this->errorCatchResponse(
                $exception,
                $errorMessage
            );
        }
    }

    /**
     * @SWG\Get(
     *   path="/skins/{id_skin}",
     *   summary="Show specific skin ",
     *   tags={"Skin"},
     *   @SWG\Parameter(
     *     name="id_skin",
     *     in="path",
     *     description="Skin ID",
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
     *                                        @SWG\Items(ref="#/definitions/Skin")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(
     *     response="403",
     *     description="Forbidden Access",
     *     @SWG\Schema(
     *       @SWG\Property(property="error", type="string",
     *                                       description="Message error",
     *                                       example="This data is not allowed
     *                                       for you"),
     *       @SWG\Property(property="code", type="integer",
     *                                      description="Response code",
     *                                      example="403"),
     *     ),
     *   ),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     * @param \App\Core\Skins\Models\Skin $skin
     * @return JsonResponse
     */
    public function show(Skin $skin): JsonResponse
    {
        $skin->load(
            [
                'regions.countriesRegions.country',
                'programSkin.datePrograms',
                'configSkin.files',
                'configSkin.texts',
                'configSkin.languages',
            ]
        );

        $data[ 'skin' ] = new SkinResource($skin);

        return $this->successResponseWithMessage($data);
    }

    /**
     * @SWG\Put(
     *   path="/skins/{id_skin}",
     *   summary="Update a skin record in RAPI",
     *   tags={"Skin"},
     *   consumes={"application/json"},
     *   @SWG\Parameter(
     *     name="id_skin",
     *     in="path",
     *     description="Skin ID",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="request",
     *     in="body",
     *     description="Request body in JSON format",
     *     type="object",
     *     @SWG\Schema(
     *         ref="#/definitions/StoreSkin",
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
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/Skin")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     * @param \App\Core\Skins\Requests\StoreSkinRequest $request
     * @param \App\Core\Skins\Models\Skin               $skin
     * @return JsonResponse
     */
    public function update(Skin $skin, StoreSkinRequest $request): ?JsonResponse
    {
        $successMessage = __('Operation Successful');
        $errorMessage   = __('An error has occurred');

        try {
            DB::beginTransaction();

            $skin->fill($request->validated());
            $skin->save();

            $this->deleteConfigSkinService->execute($skin);
            $this->deleteProgramSkinService->execute($skin);
            $this->deleteRegionSkinService->execute($skin);
            $this->storeProgramSkinService->execute($skin, $request);
            $this->storeConfigSkinService->execute($skin, $request);
            $this->storeRegionSkinService->execute($skin, $request);

            DB::commit();

            $tag = [ Skin::TAG_CACHE_MODEL, ];
            $this->forgetCacheByTag($tag);

            $skin->load(
                [
                    'regions.countriesRegions.country',
                    'programSkin.datePrograms',
                    'configSkin.files',
                    'configSkin.texts',
                    'configSkin.languages',
                ]
            );

            $data[ 'skin' ] = new SkinResource($skin);

            return $this->successResponseWithMessage($data, $successMessage);

        }
        catch(Exception $exception) {
            DB::rollBack();

            LogType::error(
                __FILE__,
                __LINE__,
                $errorMessage,
                [
                    'exception' => $exception,
                    'usersId'   => Auth::id(),
                ]
            );
            return $this->errorCatchResponse(
                $exception,
                $errorMessage
            );
        }
    }

    /**
     * @SWG\Delete(
     *   path="/skins/{id_skin}",
     *   summary="Deleted specific skin ",
     *   tags={"Skin"},
     *   @SWG\Parameter(
     *     name="id_skin",
     *     in="path",
     *     description="Skin ID",
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
     *                                        @SWG\Items(ref="#/definitions/Skin")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(
     *     response="403",
     *     description="Forbidden Access",
     *     @SWG\Schema(
     *       @SWG\Property(property="error", type="string",
     *                                       description="Message error",
     *                                       example="This data is not allowed
     *                                       for you"),
     *       @SWG\Property(property="code", type="integer",
     *                                      description="Response code",
     *                                      example="403"),
     *     ),
     *   ),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     * @param \App\Core\Skins\Models\Skin $skin
     * @return JsonResponse|null
     */
    public function destroy(Skin $skin): ?JsonResponse
    {
        $successMessage = __('Operation Successful');
        $errorMessage   = __('An error has occurred');

        try {
            DB::beginTransaction();

            $this->deleteConfigSkinService->execute($skin);
            $this->deleteProgramSkinService->execute($skin);
            $this->deleteRegionSkinService->execute($skin);
            $skin->delete();
            DB::commit();

            $tag = [ Skin::TAG_CACHE_MODEL, ];
            $this->forgetCacheByTag($tag);

            $data[ 'skin' ] = new SkinResource($skin);

            return $this->successResponseWithMessage($data, $successMessage);

        }
        catch(Exception $exception) {
            DB::rollBack();

            LogType::error(
                __FILE__,
                __LINE__,
                $errorMessage,
                [
                    'exception' => $exception,
                    'usersId'   => Auth::id(),
                ]
            );
            return $this->errorCatchResponse(
                $exception,
                $errorMessage
            );
        }
    }
}
