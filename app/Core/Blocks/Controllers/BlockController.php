<?php

namespace App\Core\Blocks\Controllers;

use App\Core\Blocks\Models\Block;
use App\Core\Base\Classes\ModelConst;
use App\Core\Base\Services\TranslateArrayService;
use App\Core\Base\Traits\CacheUtilsTraits;
use App\Core\Blocks\Collections\BlockCollection;
use App\Core\Blocks\Requests\StoreBlockRequest;
use App\Core\Blocks\Resources\BlockResource;
use App\Core\Languages\Resources\LanguageResource;
use App\Core\Countries\Resources\RegionRapiResource;
use App\Core\Terms\Models\Language;
use App\Core\Countries\Models\RegionRapi;
use App\Core\Blocks\Services\CheckBlockProductsService;
use App\Core\Blocks\Services\CheckBlockService;
use App\Core\Blocks\Services\SetModelBlockableService;
use App\Core\Blocks\Services\SetModelEntityableService;
use App\Core\Base\Services\LogType;
use App\Core\Base\Traits\ApiResponser;
use App\Http\Controllers\Controller;
use Auth;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Swagger\Annotations as SWG;

class BlockController extends Controller
{
    use ApiResponser;
    use CacheUtilsTraits;

    /**
     * @var \App\Core\Blocks\Services\SetModelBlockableService
     */
    private $setModelBlockableService;
    /**
     * @var \App\Core\Blocks\Services\SetModelEntityableService
     */
    private $setModelEntityableService;

    public function __construct(
        SetModelBlockableService $setModelBlockableService,
        SetModelEntityableService $setModelEntityableService
    ) {
        $this->middleware('client.credentials');
        $this->middleware('check.external_access')
            ->except('check');
        $this->setModelBlockableService  = $setModelBlockableService;
        $this->setModelEntityableService = $setModelEntityableService;
    }

    /**
     * @SWG\Get(
     *   path="/blocks",
     *   summary="Show blocks list ",
     *   tags={"Block"},
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
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/Block")),
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
        $relation = [ 'entityable', 'blockable' ];

        $block = Block::query()
            ->with($relation)
            ->paginateByRequest();

        $blocksCollection = new BlockCollection($block);
        $data[ 'blocks' ] = $blocksCollection;

        return $this->successResponseWithMessage($data);
    }

    /**
     * @SWG\Get(
     *   path="/blocks/create",
     *   summary="Get info necessary to store blocks",
     *   tags={"Block"},
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
        $regions              = RegionRapi::all();
        $regions              = RegionRapiResource::collection($regions);
        $languages            = Language::all();
        $languages            = LanguageResource::collection($languages);
        $typeBlockables       = TranslateArrayService::execute(ModelConst::BLOCKEABLE_LIST);
        $typeEntities         = TranslateArrayService::execute(ModelConst::ENTITYABLE_LIST);
        $typeBlocks           = TranslateArrayService::execute(ModelConst::TYPE_BLOCK_LIST);
        $typeBlockTypeProduct = TranslateArrayService::execute(ModelConst::TYPE_BLOCK_TYPE_PRODUCT_LIST);

        $data = [
            'regions'                 => $regions,
            'languages'               => $languages,
            'type_blocks'             => $typeBlocks,
            'type_blockeables'        => $typeBlockables,
            'type_entities'           => $typeEntities,
            'type_block_type_product' => $typeBlockTypeProduct,
        ];

        return $this->successResponseWithMessage($data);
    }

    /**
     * @SWG\Post(
     *   path="/blocks",
     *   summary="Store new block with regions and config ",
     *   tags={"Block"},
     *   consumes={"application/json"},
     *
     *
     *   @SWG\Parameter(
     *     name="EXAMPLES REQUESTS",
     *     in="body",
     *     description="EXAMPLES REQUESTS",
     *     type="object",
     *     @SWG\Schema(
     *         ref="#/definitions/StoreBlockExamplesRequest",
     *     )
     *   ),
     *
     *   @SWG\Parameter(
     *     name="request",
     *     in="body",
     *     description="request body json",
     *     type="object",
     *     @SWG\Schema(
     *         ref="#/definitions/StoreBlockRequest",
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
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/Block")),
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
    public function store(StoreBlockRequest $storeBlockRequest)
    {
        $errorMessage   = __('Unexpected error occurred while trying to process your request.');
        $successMessage = __('Successful registration');

        try {
            DB::beginTransaction();

            $block = new  Block();
            $block->fill($storeBlockRequest->validated());
            $block = $this->setModelBlockableService->execute($block, $storeBlockRequest);
            $block = $this->setModelEntityableService->execute($block, $storeBlockRequest);
            $block->save();

            DB::commit();

            $block->load([ 'entityable', 'blockable' ]);

            $tag = [ Block::TAG_CACHE_MODEL, ];
            $this->forgetCacheByTag($tag);
            $data[ 'block' ] = new BlockResource($block);
            return $this->successResponseWithMessage($data, $successMessage, Response::HTTP_CREATED);
        }
        catch(Exception $exception) {
            DB::rollBack();
            LogType::error(
                __FILE__, __LINE__, $errorMessage, [
                    'exception' => $exception,
                    'usersId'   => Auth::id(),
                ]
            );
            return $this->errorCatchResponse($exception, $errorMessage, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @SWG\Get(
     *   path="/blocks/{id_block}",
     *   summary="Show specific block ",
     *   tags={"Block"},
     *   @SWG\Parameter(
     *     name="id_block",
     *     in="path",
     *     description="Block ID",
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
     *                                        @SWG\Items(ref="#/definitions/Block")),
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
     * @param \App\Core\Blocks\Models\Block $block
     * @return JsonResponse
     */
    public function show(Block $block)
    {
        $block->load([ 'entityable', 'blockable' ]);
        $data[ 'block' ] = new BlockResource($block);

        return $this->successResponseWithMessage($data);
    }

    /**
     * @SWG\Put(
     *   path="/blocks/{id_block}",
     *   summary="Update record on blocks ",
     *   tags={"Block"},
     *   consumes={"application/json"},
     *
     *   @SWG\Parameter(
     *     name="id_block",
     *     in="path",
     *     description="Block ID",
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
     *         ref="#/definitions/StoreBlockRequest",
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
     *                                        @SWG\Items(ref="#/definitions/Block")),
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
     * @param \App\Core\Blocks\Requests\StoreBlockRequest $request
     * @param Block                                       $block
     * @return JsonResponse
     */
    public function update(Block $block, StoreBlockRequest $storeBlockRequest)
    {
        $errorMessage   = __('Unexpected error occurred while trying to process your request.');
        $successMessage = __('Successful update');
        try {
            DB::beginTransaction();

            $block->fill($storeBlockRequest->validated());
            $block = $this->setModelBlockableService->execute($block, $storeBlockRequest);
            $block = $this->setModelEntityableService->execute($block, $storeBlockRequest);
            $block->save();

            DB::commit();

            $block->load([ 'entityable', 'blockable' ]);

            $tag = [ Block::TAG_CACHE_MODEL, ];
            $this->forgetCacheByTag($tag);

            $data[ 'block' ] = new BlockResource($block);
            return $this->successResponseWithMessage($data, $successMessage);
        }
        catch(Exception $exception) {
            DB::rollBack();
            LogType::error(
                __FILE__, __LINE__, $errorMessage, [
                    'exception' => $exception,
                    'usersId'   => Auth::id(),
                ]
            );
            return $this->errorCatchResponse($exception, $errorMessage, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @SWG\Delete(
     *   path="/blocks/{id_block}",
     *   summary="Deleted specific block ",
     *   tags={"Block"},
     *   @SWG\Parameter(
     *     name="id_block",
     *     in="path",
     *     description="Block ID",
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
     *                                        @SWG\Items(ref="#/definitions/Block")),
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
    public function destroy(Block $block)
    {
        $errorMessage   = __('Unexpected error occurred while trying to process your request.');
        $successMessage = __('Successful delete');
        try {
            DB::beginTransaction();

            $block->delete();

            DB::commit();

            $tag = [ Block::TAG_CACHE_MODEL, ];
            $this->forgetCacheByTag($tag);

            $data[ 'block' ] = new BlockResource($block);

            return $this->successResponseWithMessage($data, $successMessage);
        }
        catch(Exception $exception) {
            DB::rollBack();
            LogType::error(
                __FILE__, __LINE__, $errorMessage, [
                    'exception' => $exception,
                    'usersId'   => Auth::id(),
                ]
            );
            return $this->errorCatchResponse($exception, $errorMessage, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @SWG\Get(
     *   path="/blocks/check",
     *   summary="Show check list elements blocks",
     *   tags={"Block"},
     *   @SWG\Parameter(
     *     name="user_ip",
     *     in="query",
     *     description="user ip",
     *     type="integer",
     *   ),
     *   @SWG\Parameter(
     *     name="code_language",
     *     in="query",
     *     description="code_language",
     *     type="integer",
     *   ),
     *   @SWG\Parameter(
     *     name="affiliate",
     *     in="query",
     *     description="affiliate",
     *     type="integer",
     *   ),
     *   @SWG\Parameter(
     *     name="product",
     *     in="query",
     *     description="product",
     *     type="integer",
     *   ),
     *   @SWG\Parameter(
     *     name="type_product",
     *     in="query",
     *     description="type_product",
     *     type="integer",
     *   ),
     *   @SWG\Parameter(
     *     name="list_product",
     *     in="query",
     *     description="list_product",
     *     type="integer",
     *   ),
     *   security={
     *     {"Key-access": {},"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/Block")),
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
    public function check(Request $request)
    {
        $checkBlockService = new CheckBlockService();
        $checkBlockProductsService = new CheckBlockProductsService();
        $validationCollection = $checkBlockService->execute($request);
        $validationProductCollection = $checkBlockProductsService->execute($request);
        $validationCollection = $validationCollection->merge($validationProductCollection);

        return $this->successResponseWithMessage($validationCollection);
    }

}
