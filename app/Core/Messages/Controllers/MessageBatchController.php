<?php

namespace App\Core\Messages\Controllers;

use App\Core\Base\Classes\ModelConst;
use App\Core\Base\Services\TranslateArrayService;
use App\Core\Messages\Collections\MessageBatchCollection;
use App\Core\Messages\Collections\MessageTemplateCollection;
use App\Core\Messages\Requests\MassMessageRequest;
use App\Core\Messages\Resources\MessageBatchResource;
use App\Core\Messages\Models\MessageTemplate;
use App\Core\Messages\Services\GetMessageBatchRoyalPanelService;
use App\Core\Messages\Services\SendMessageService;
use App\Core\Messages\Services\UpdateMessagesByUserService;
use App\Core\Base\Services\LogType;
use App\Core\Messages\Services\ValidateMessagesByUserService;
use App\Http\Controllers\Controller;
use App\Core\Rapi\Models\System;
use App\Core\Base\Traits\ApiResponser;
use App\Core\Rapi\Transforms\SystemTransformer;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class MessageBatchController extends Controller
{
    use ApiResponser;

    /**
     * @var \App\Core\Messages\Services\SendMessageService
     */
    private $sendMessageService;
    /**
     * @var GetMessageBatchRoyalPanelService
     */
    private $getMessageBatchRoyalPanelService;


    public function __construct(
        GetMessageBatchRoyalPanelService $getMessageBatchRoyalPanelService,
        SendMessageService $sendMessageService
    )
    {
        $this->middleware('client.credentials');
        $this->middleware('check.external_access');
        $this->getMessageBatchRoyalPanelService = $getMessageBatchRoyalPanelService;
        $this->sendMessageService=$sendMessageService;
    }



    /**
     * @SWG\Get(
     *   path="/message_batch",
     *   summary="List Messages Batch by category, template ,date ",
     *   tags={"Message Batch"},
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
     *     name="system",
     *     in="query",
     *     description="System Id",
     *     type="integer",
     *   ),
     *   @SWG\Parameter(
     *     name="date_init",
     *     in="query",
     *     description="Date Initial",
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="date_end",
     *     in="query",
     *     description="Date End",
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="status",
     *     in="query",
     *     description="Status (0: scheduled; 1: sent; 2: canceled)",
     *     type="integer",
     *   ),
     *   @SWG\Parameter(
     *     name="category",
     *     in="query",
     *     description="Category ID (1: Promo; 2: Internal)",
     *     type="integer",
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
     *                                        @SWG\Items(ref="#/definitions/MessageBatch")),
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
        $messageBatch=$this->getMessageBatchRoyalPanelService->execute($request);
        $messageBatchCollection = new MessageBatchCollection($messageBatch);

        $data[ 'batch' ] = $messageBatchCollection;
        return $this->successResponseWithMessage($data);
    }

    /**
     * @SWG\Get(
     *   path="/message_batch/create",
     *   summary="Get info necesary to create Message Batch ",
     *   tags={"Message Batch"},
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
    public function create(){
        $templates = MessageTemplate::paginateFromCacheByRequest(['*'],MessageTemplate::TAG_CACHE_MODEL);
        $templates = new MessageTemplateCollection($templates);
        $system     = System::all();
        $system     = fractal($system,new SystemTransformer)->toArray();
        $data       = [
            'templates' => $templates,
            'system'    => $system,
            'status'    => TranslateArrayService::execute(ModelConst::MESSAGE_BATCH_STATUS_RANGE)
        ];
        return $this->successResponseWithMessage($data) ;
    }

    /**
     * @SWG\Post(
     *   path="/message_batch",
     *   summary="Send Mass Message ",
     *   consumes={"multipart/form-data"},
     *   tags={"Message Batch"},
     *   @SWG\Parameter(
     *     name="system",
     *     in="query",
     *     description="System ID",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="template",
     *     in="query",
     *     description="Template ID",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="send_date",
     *     in="query",
     *     description="Send Date",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="final_date",
     *     in="query",
     *     description="Promotion Final Date",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="csv_file",
     *     in="formData",
     *     description="CSV File",
     *     required=true,
     *     type="file"
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
     *                                        @SWG\Items(ref="#/definitions/MessageBatch")),
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
    public function store(MassMessageRequest $request)
    {
        $successMessage = __('Operation Successful');
        $errorMessage   = __('An error has occurred');

        try {
            DB::beginTransaction();

            $messageBatch= $this->sendMessageService->execute($request);

            $data[ 'messageBatch' ] =new MessageBatchResource($messageBatch);

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
