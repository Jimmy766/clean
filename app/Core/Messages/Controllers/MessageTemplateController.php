<?php

namespace App\Core\Messages\Controllers;

use App\Core\Messages\Collections\MessageTemplateCollection;
use App\Core\Messages\Requests\StoreMessageTemplateRequest;
use App\Core\Messages\Resources\MessageTemplateCategoryResource;
use App\Core\Messages\Resources\MessageTemplateResource;
use App\Core\Messages\Resources\MessageTemplateTypeResource;
use App\Core\Messages\Models\MessageTemplate;
use App\Core\Messages\Models\MessageTemplateCategory;
use App\Core\Messages\Models\MessageTemplateType;
use App\Core\Messages\Services\SearchMessageTemplateService;
use App\Core\Messages\Services\StoreMessageTemplateService;
use App\Core\Base\Services\LogType;
use App\Http\Controllers\Controller;
use App\Core\Rapi\Models\System;
use App\Core\Base\Traits\ApiResponser;
use App\Core\Rapi\Transforms\SystemTransformer;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class MessageTemplateController extends Controller
{
    use ApiResponser;


    /**
     * @var StoreMessageTemplateService
     */
    private $storeMessageTemplateService;

    /**
     * @var SearchMessageTemplateService
     */
    private $searchMessageTemplateService;

    public function __construct(
        SearchMessageTemplateService $searchMessageTemplateService,
        StoreMessageTemplateService $storeMessageTemplateService
        )
    {
        $this->middleware('client.credentials');
        $this->middleware('check.external_access');
        $this->searchMessageTemplateService = $searchMessageTemplateService;
        $this->storeMessageTemplateService = $storeMessageTemplateService;
    }


    /**
     * @SWG\Get(
     *   path="/message_templates",
     *   summary="List Message Templates ",
     *   tags={"Message Templates"},
     *   @SWG\Parameter(
     *     name="system_id",
     *     in="query",
     *     description="System ID",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="site_id",
     *     in="query",
     *     description="Site ID",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="language",
     *     in="query",
     *     description="Language",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="subject",
     *     in="query",
     *     description="Subject",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="name",
     *     in="query",
     *     description="Name",
     *     required=false,
     *     type="string"
     *   ),
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
     *                                        @SWG\Items(ref="#/definitions/MessageTemplate")),
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
    public function index(Request $request)
    {
	    $templates= $this->searchMessageTemplateService->execute($request);
	    $data[ 'templates' ] = new MessageTemplateCollection($templates);

	    return $this->successResponseWithMessage($data);

    }
    /**
     * @SWG\Get(
     *   path="/message_templates/create",
     *   summary="Get info necessary to store Message Templates",
     *   tags={"Message Templates"},
     *   security={
     *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(response=200, ref="#/responses/200"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function create()
    {
	    $categories = MessageTemplateCategory::all();
	    $categories = MessageTemplateCategoryResource::collection($categories);
	    $types      = MessageTemplateType::all();
	    $types      = MessageTemplateTypeResource::collection($types);
	    $system     = System::all();
	    $system     = fractal($system,new SystemTransformer)->toArray();
	    $data       = [
		    'categories' => $categories,
		    'types'      => $types,
		    'system'     => $system['data'],
	    ];
	    return $this->successResponseWithMessage($data) ;
    }


    /**
     * @SWG\Post(
     *   path="/message_templates",
     *   summary="Store Message Template ",
     *   tags={"Message Templates"},
     *   @SWG\Parameter(
     *     name="name",
     *     in="query",
     *     description="Template Name",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="type",
     *     in="query",
     *     description="Template Type ID",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="category",
     *     in="query",
     *     description="Template Category ID",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="system",
     *     in="query",
     *     description="System ID",
     *     required=true,
     *     type="integer"
     *   ),
     *   security={
     *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="object",
     *                                        ref="#/definitions/MessageTemplate")
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
    public function store(StoreMessageTemplateRequest $request)
    {
        $successMessage = __('Operation Successful');
        $errorMessage   = __('An error has occurred');

        try {
            DB::beginTransaction();

            $messageTemplate= $this->storeMessageTemplateService->execute($request);

            DB::commit();

            $messageTemplate->load(['messageTemplateLanguage','messageTemplateCategory','messageTemplateType']);
            $data[ 'template' ] =new MessageTemplateResource($messageTemplate);

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
     * @SWG\Get(
     *   path="/message_templates/{id_template}",
     *   summary="Show specific message template ",
     *   tags={"Message Templates"},
     *   @SWG\Parameter(
     *     name="id_template",
     *     in="path",
     *     description="Message Template ID",
     *     required=true,
     *     type="integer"
     *   ),
     *   security={
     *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="object",
     *                                        ref="#/definitions/MessageTemplate")
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
     * @param \App\Core\Messages\Models\MessageTemplate $messageTemplate
     * @return JsonResponse
     */
    public function show(MessageTemplate $messageTemplate)
    {

        $messageTemplate->load(['messageTemplateLanguage','messageTemplateCategory','messageTemplateType']);
        $data[ 'template' ] = new MessageTemplateResource($messageTemplate);

        return $this->successResponseWithMessage($data);
    }

    /**
     * @SWG\Put(
     *   path="/message_templates/{id_template}",
     *   summary="Update Message Template ",
     *   tags={"Message Templates"},
     *   @SWG\Parameter(
     *     name="id_template",
     *     in="path",
     *     description="Message Template ID",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="name",
     *     in="query",
     *     description="Template Name",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="type",
     *     in="query",
     *     description="Template Type ID",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="category",
     *     in="query",
     *     description="Template Category ID",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="system",
     *     in="query",
     *     description="System ID",
     *     required=true,
     *     type="integer"
     *   ),
     *   security={
     *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="object",
     *                                        ref="#/definitions/MessageTemplate")
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
    public function update(MessageTemplate $messageTemplate,StoreMessageTemplateRequest $request)
    {
        $successMessage = __('Operation Successful');
        $errorMessage   = __('An error has occurred');

        try {
            DB::beginTransaction();

            $messageTemplate->fill($request->validated());
            $messageTemplate->save();

            DB::commit();

            $messageTemplate->load(['messageTemplateLanguage','messageTemplateCategory','messageTemplateType']);
            $data[ 'template' ] =new MessageTemplateResource($messageTemplate);

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
     *   path="/message_templates/{id_template}",
     *   summary="Delete specific message template ",
     *   tags={"Message Templates"},
     *   @SWG\Parameter(
     *     name="id_template",
     *     in="path",
     *     description="Message Template ID",
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
     *         @SWG\Property(property="data",
     *              type="object",
     *              ref="#/definitions/MessageTemplate")
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
     * @param MessageTemplate $messageTemplate
     * @return JsonResponse
     */
    public function destroy(MessageTemplate $messageTemplate)
    {
        $successMessage = __('Operation Successful');
        $errorMessage   = __('An error has occurred');

        try {
            DB::beginTransaction();

            $messageTemplate->delete();
            DB::commit();

            $data[ 'template' ] =new MessageTemplateResource($messageTemplate);

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
