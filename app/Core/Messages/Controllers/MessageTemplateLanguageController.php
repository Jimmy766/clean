<?php

namespace App\Core\Messages\Controllers;

use App\Core\Messages\Collections\MessageTemplateCollection;
use App\Core\Languages\Resources\LanguageTrillonarioResource;
use App\Core\Messages\Resources\MessageTemplateResource;
use App\Core\Terms\Models\LanguageTrillonario;
use App\Core\Messages\Models\MessageTemplate;
use App\Http\Controllers\Controller;
use App\Core\Rapi\Models\Site;
use App\Core\Rapi\Transforms\SiteTransformer;
use Exception;
use App\Core\Base\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Core\Base\Services\LogType;
use App\Core\Messages\Models\MessageTemplateLanguage;
use Illuminate\Http\JsonResponse;
use DB;
use Illuminate\Support\Facades\Auth;
use App\Core\Messages\Services\StoreMessageTemplateLanguageService;
use App\Core\Messages\Resources\MessageTemplateLanguageResource;
use App\Core\Messages\Requests\StoreMessageTemplateLanguageRequest;
use App\Core\Messages\Collections\MessageTemplateLanguageCollection;


class MessageTemplateLanguageController extends Controller
{
    use ApiResponser;


    /**
     * @var StoreMessageTemplateLanguageService
     */
    private $storeMessageTemplateLanguageService;



    public function __construct(
        StoreMessageTemplateLanguageService $storeMessageTemplateLanguageService
        )
    {
        $this->middleware('client.credentials');
        $this->middleware('check.external_access');
        $this->storeMessageTemplateLanguageService = $storeMessageTemplateLanguageService;
    }

    /**
     * @SWG\Get(
     *   path="/message_template_languages",
     *   summary="List Message Template Languages ",
     *   tags={"Message Template Languages"},
     *   security={
     *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array",
     *                                        @SWG\Items(ref="#/definitions/MessageTemplateLanguage")),
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
    public function index()
    {
        $templates=MessageTemplateLanguage::paginateByRequest();
        $data[ 'templates' ] = new MessageTemplateLanguageCollection($templates);

        return $this->successResponseWithMessage($data);
    }
    /**
     * @SWG\Get(
     *   path="/message_template_languages/create",
     *   summary="Get info necessary to store Message Template Languages",
     *   tags={"Message Template Languages"},
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
     *     name="template_name",
     *     in="query",
     *     description="Template Name",
     *     type="string"
     *   ),
     *   security={
     *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{}}
     *   },
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
	    $template_name  = $request->template_name;
	    $templatesQuery = MessageTemplate::query();
	    if ($template_name !== null && $template_name !== '') {
		    $templatesQuery = $templatesQuery->where('template_name', 'like', '%' . $template_name . '%');
	    }
	    $templates = $templatesQuery->paginateByRequest();

	    $templates = new MessageTemplateCollection($templates);

	    $languages = LanguageTrillonario::all();
	    $languages = LanguageTrillonarioResource::collection($languages);

	    $sites = Site::where('sys_id', 1)->where('wlabel', 0)->get();
	    $sites = \fractal($sites, new SiteTransformer)->toArray();
	    $data  = [
		    'templates' => $templates,
		    'languages'   => $languages,
		    'sites'      => $sites['data'],
	    ];
	    return $this->successResponseWithMessage($data) ;
    }

    /**
     * @SWG\Get(
     *   path="/message_template_languages/{id_template_language}",
     *   summary="Show specific message template language ",
     *   tags={"Message Template Languages"},
     *   @SWG\Parameter(
     *     name="id_template_language",
     *     in="path",
     *     description="Message Template Language ID",
     *     required=true,
     *     type="integer"
     *   ),
     *   security={
     *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="object",
     *                                        ref="#/definitions/MessageTemplateLanguage")
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
     * @param \App\Core\Messages\Models\MessageTemplateLanguage $messageTemplateLanguage
     * @return JsonResponse
     */
    public function show(MessageTemplateLanguage $messageTemplateLanguage)
    {

        $data[ 'template' ] = new MessageTemplateLanguageResource($messageTemplateLanguage);

        return $this->successResponseWithMessage($data);
    }
    /**
     * @SWG\Post(
     *   path="/message_template_languages",
     *   summary="Store Message Template Language ",
     *   tags={"Message Template Languages"},
     *   @SWG\Parameter(
     *     name="language",
     *     in="formData",
     *     description="Template Language ID",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="subject",
     *     in="formData",
     *     description="Template Subject",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="template_id",
     *     in="formData",
     *     description="Template ID",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="body",
     *     in="formData",
     *     description="Body",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="site_id",
     *     in="formData",
     *     description="Site ID",
     *     required=false,
     *     type="integer"
     *   ),
     *  security={
     *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="object",
     *                                        ref="#/definitions/MessageTemplateLanguage")
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
    public function store(StoreMessageTemplateLanguageRequest $request)
    {
        $successMessage = __('Operation Successful');
        $errorMessage   = __('An error has occurred');

        try {
            DB::beginTransaction();

            $messageTemplateLanguageLanguage= $this->storeMessageTemplateLanguageService->execute($request);

            $data[ 'messageTemplateLanguage' ] = new MessageTemplateLanguageResource($messageTemplateLanguageLanguage);

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
     * @SWG\Put(
     *   path="/message_template_languages/{id_template_language}",
     *   summary="Update Message Template ",
     *   tags={"Message Template Languages"},
     *   @SWG\Parameter(
     *     name="id_template_language",
     *     in="path",
     *     description="Template Language ID",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="language",
     *     in="formData",
     *     description="Template Language ID",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="subject",
     *     in="formData",
     *     description="Template Subject",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="template_id",
     *     in="formData",
     *     description="Template ID",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="body",
     *     in="formData",
     *     description="Body",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="site_id",
     *     in="formData",
     *     description="Site ID",
     *     required=false,
     *     type="integer"
     *   ),
     *   security={
     *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="object",
     *                                        ref="#/definitions/MessageTemplateLanguage")
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
    public function update(MessageTemplateLanguage $messageTemplateLanguage,StoreMessageTemplateLanguageRequest $request)
    {
        $successMessage = __('Operation Successful');
        $errorMessage   = __('An error has occurred');

        try {
            DB::beginTransaction();

            $messageTemplateLanguage->fill($request->validated());
            $messageTemplateLanguage->save();

            DB::commit();

            $data[ 'template' ] =new MessageTemplateLanguageResource($messageTemplateLanguage);

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
     *   path="/message_template_languages/{id_template_language}",
     *   summary="Delete specific message template ",
     *   tags={"Message Template Languages"},
     *   @SWG\Parameter(
     *     name="id_template_language",
     *     in="path",
     *     description="Message Template ID",
     *     required=true,
     *     type="string"
     *   ),
     *   security={
     *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data",
     *              type="object",
     *              ref="#/definitions/MessageTemplateLanguage")
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
     * @param MessageTemplateLanguage $messageTemplateLanguage
     * @return JsonResponse
     */
    public function destroy(MessageTemplateLanguage $messageTemplateLanguage)
    {
        $successMessage = __('Operation Successful');
        $errorMessage   = __('An error has occurred');

        try {
            DB::beginTransaction();

            $messageTemplateLanguage->delete();
            DB::commit();

            $data[ 'template' ] =new MessageTemplateLanguageResource($messageTemplateLanguage);

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
