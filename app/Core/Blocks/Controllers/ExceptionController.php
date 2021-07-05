<?php

namespace App\Core\Blocks\Controllers;

use App\Core\Base\Services\TranslateArrayService;
use App\Core\Blocks\Models\ExceptionBlock;
use App\Core\Base\Classes\ModelConst;
use App\Core\Base\Traits\CacheUtilsTraits;
use App\Core\Rapi\Collections\ExceptionCollection;
use App\Core\Rapi\Requests\StoreExceptionRequest;
use App\Core\Rapi\Resources\ExceptionResource;
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

class ExceptionController extends Controller
{
    use ApiResponser;
    use CacheUtilsTraits;

    public function __construct()
    {
        $this->middleware('client.credentials');
        $this->middleware('check.external_access');
    }

    /**
     * @SWG\Get(
     *   path="/exceptions",
     *   summary="Show exceptions list ",
     *   tags={"Exception"},
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
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/Exception")),
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
        $exception = ExceptionBlock::query()->paginateByRequest();

        $exceptionsCollection = new ExceptionCollection($exception);
        $data[ 'exceptions' ] = $exceptionsCollection;

        return $this->successResponseWithMessage($data);
    }

    /**
     * @SWG\Get(
     *   path="/exceptions/create",
     *   summary="Get info necessary to store exceptions",
     *   tags={"Exception"},
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
        $typeExceptions = TranslateArrayService::execute(ModelConst::TYPE_EXCEPTION_LIST);

        $data = [
            'type_exceptions' => $typeExceptions,
        ];

        return $this->successResponseWithMessage($data);
    }

    /**
     * @SWG\Post(
     *   path="/exceptions",
     *   summary="Store new exception with regions and config ",
     *   tags={"Exception"},
     *   consumes={"application/json"},
     *
     *   @SWG\Parameter(
     *     name="request",
     *     in="body",
     *     description="request body json",
     *     type="object",
     *     @SWG\Schema(
     *         ref="#/definitions/StoreExceptionRequest",
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
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/Exception")),
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
     * @param StoreExceptionRequest $storeExceptionRequest
     * @return JsonResponse
     */
    public function store(StoreExceptionRequest $storeExceptionRequest)
    {
        $errorMessage   = __('Unexpected error occurred while trying to process your request.');
        $successMessage = __('Successful registration');

        try {
            DB::beginTransaction();

            $exception = new  ExceptionBlock();
            $exception->fill($storeExceptionRequest->validated());
            $exception->save();

            DB::commit();

            $tag = [ ExceptionBlock::TAG_CACHE_MODEL, ];
            $this->forgetCacheByTag($tag);
            $data[ 'exception' ] = new ExceptionResource($exception);
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
     *   path="/exceptions/{id_exception}",
     *   summary="Show specific exception ",
     *   tags={"Exception"},
     *   @SWG\Parameter(
     *     name="id_exception",
     *     in="path",
     *     description="Exception ID",
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
     *                                        @SWG\Items(ref="#/definitions/Exception")),
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
     * @param Exception $exception
     * @return JsonResponse
     */
    public function show(ExceptionBlock $exception)
    {
        $data[ 'exception' ] = new ExceptionResource($exception);

        return $this->successResponseWithMessage($data);
    }

    /**
     * @SWG\Put(
     *   path="/exceptions/{id_exception}",
     *   summary="Update record on exceptions ",
     *   tags={"Exception"},
     *   consumes={"application/json"},
     *
     *   @SWG\Parameter(
     *     name="id_exception",
     *     in="path",
     *     description="Exception ID",
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
     *         ref="#/definitions/StoreExceptionRequest",
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
     *                                        @SWG\Items(ref="#/definitions/Exception")),
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
     * @param \App\Core\Rapi\Requests\StoreExceptionRequest $request
     * @param \App\Core\Blocks\Models\ExceptionBlock        $exception
     * @return JsonResponse
     */
    public function update(ExceptionBlock $exception, StoreExceptionRequest $storeExceptionRequest)
    {
        $errorMessage   = __('Unexpected error occurred while trying to process your request.');
        $successMessage = __('Successful update');
        try {
            DB::beginTransaction();

            $exception->fill($storeExceptionRequest->validated());
            $exception->save();

            DB::commit();

            $tag = [ ExceptionBlock::TAG_CACHE_MODEL, ];
            $this->forgetCacheByTag($tag);

            $data[ 'exception' ] = new ExceptionResource($exception);
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
     *   path="/exceptions/{id_exception}",
     *   summary="Deleted specific exception ",
     *   tags={"Exception"},
     *   @SWG\Parameter(
     *     name="id_exception",
     *     in="path",
     *     description="Exception ID",
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
     *                                        @SWG\Items(ref="#/definitions/Exception")),
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
     * @param \App\Core\Blocks\Models\ExceptionBlock $exception
     * @return JsonResponse
     */
    public function destroy(ExceptionBlock $exception)
    {
        $errorMessage   = __('Unexpected error occurred while trying to process your request.');
        $successMessage = __('Successful delete');
        try {
            DB::beginTransaction();

            $exception->delete();

            DB::commit();

            $tag = [ ExceptionBlock::TAG_CACHE_MODEL, ];
            $this->forgetCacheByTag($tag);

            $data[ 'exception' ] = new ExceptionResource($exception);

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
}
