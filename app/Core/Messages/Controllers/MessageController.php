<?php

namespace App\Core\Messages\Controllers;

use App\Core\Messages\Collections\MessageCollection;
use App\Core\Messages\Resources\MessageResource;
use App\Core\Messages\Models\Message;
use App\Core\Messages\Models\MessageTemplateCategory;
use App\Core\Messages\Services\SendMessageService;
use App\Core\Messages\Services\UpdateMessagesByUserService;
use App\Core\Messages\Services\UpdateReadMessagesByUserService;
use App\Core\Base\Services\LogType;
use App\Core\Messages\Services\ValidateMessagesByUserService;
use App\Core\Base\Traits\ApiResponser;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class MessageController extends Controller
{
    use ApiResponser;

    /**
     * @var \App\Core\Messages\Services\ValidateMessagesByUserService
     */
    private $validateMessagesByUserService;
    /**
     * @var UpdateMessagesByUserService
     */
    private $updateMessagesByUserService;

    /**
     * @var \App\Core\Messages\Services\UpdateMessagesByUserService
     */
    private $updateReadMessagesByUserService;


    public function __construct(
        ValidateMessagesByUserService $validateMessagesByUserService,
        UpdateMessagesByUserService $updateMessagesByUserService,
        UpdateReadMessagesByUserService $updateReadMessagesByUserService
        )
    {
        $this->middleware('auth:api');
        $this->middleware('client.credentials');
        $this->validateMessagesByUserService = $validateMessagesByUserService;
        $this->updateMessagesByUserService = $updateMessagesByUserService;
        $this->updateReadMessagesByUserService = $updateReadMessagesByUserService;
    }

    /**
     * @SWG\Get(
     *   path="/messages/list-by-user",
     *   summary="List Messages by user ",
     *   tags={"Message"},
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
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array",
     *                                        @SWG\Items(ref="#/definitions/Messages")),
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
    public function listMessagesByUser(Request $request)
    {
        $user     = Auth::user();
        $userId   = $user->usr_id;
        $messages = Message::where('message_deleted', '=', 0)
            ->where('message_type', '<>', 1)
            ->where('usr_id', $userId)
            ->latest('message_date_received')
            ->paginateByRequest();

        $messageCollection = new MessageCollection($messages);

        $data[ 'messages' ] = $messageCollection;

        return $this->successResponseWithMessage($data);
    }


    /**
     * @SWG\Get(
     *   path="/messages/latest-by-user",
     *   summary="Latest Message by user ",
     *   tags={"Message"},
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data",
     *           ref="#/definitions/Messages",
     *           type="object")
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
    public function latestMessagesByUser(Request $request)
    {
        $user     = Auth::user();
        $userId   = $user->usr_id;
        $message = Message::where('message_deleted', '=', 0)
            ->where('message_read', '=', 0)
            ->where('message_type', '<>', 1)
            ->where('usr_id', $userId)
            ->latest('message_date_received')
            ->first();

        $message=$message === null ? []: new MessageResource($message);

        $data[ 'message' ] = $message;

        return $this->successResponseWithMessage($data);
    }


    /**
     * @SWG\Get(
     *   path="/messages/count-by-user",
     *   summary="count Messages by user ",
     *   tags={"Message"},
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array",
     *                                        @SWG\Items(ref="#/definitions/Messages")),
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
    public function countMessagesByUser(Request $request)
    {
        $user     = Auth::user();
        $userId   = $user->usr_id;
        $messagesQuery=Message::query()
	        ->where('messages.message_deleted', '=', 0)
	        ->where('messages.message_read', '=', 0)
	        ->where('messages.message_type', '<>', 1)
	        ->where('messages.usr_id', $userId)
	        ->join('messages_batch as mb','mb.batch_id','=','messages.batch_id')
	        ->join('messages_templates as mt','mt.template_id','=','mb.template_id')
	        ->join('messages_templates_categories as mtc','mt.template_category','=','mtc.category_id')
	        ->where(function ($query){
	        	$query->whereDate('mb.final_date','=','0000-00-00')
			        ->orWhereDate('mb.final_date', '>=', Carbon::now());
	        });

	    $data['messages'] = $messagesQuery->get([DB::raw('count(if(mtc.category_id=1, 1,null)) as promo,
            count(if(mtc.category_id=2, 1,null)) as internal')])
		    ->first();

        return $this->successResponseWithMessage($data);
    }

    /**
     * @SWG\Get(
     *   path="/messages/{message_id}/one-by-user",
     *   summary="Show Message by user ",
     *   tags={"Message"},
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Parameter(
     *     name="message_id",
     *     in="path",
     *     description="Message ID",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array",
     *                                        @SWG\Items(ref="#/definitions/Messages")),
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
    public function OneMessageByUser(Message $message, Request $request)
    {
        $user    = Auth::user();
        $userId   = $user->usr_id;
        $message = Message::where('usr_id', $userId)
            ->where('message_id', '=', $message->message_id)
            ->where('message_deleted', '=', 0)
            ->where('message_type', '<>', 1)
            ->first();

        $messageResource = $message === null ? [] : new MessageResource($message);

        $data[ 'message' ] = $messageResource;

        return $this->successResponseWithMessage($data);
    }

    /**
     * @SWG\Put(
     *   path="/messages/{message_id}/mark-read-message",
     *   summary="Update record mark read on messages ",
     *   tags={"Message"},
     *   consumes={"application/x-www-form-urlencoded"},
     *   @SWG\Parameter(
     *     name="message_id",
     *     in="path",
     *     description="Message ID",
     *     required=true,
     *     type="string"
     *   ),
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array",
     *                                        @SWG\Items(ref="#/definitions/Messages")),
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
     * @param \App\Core\Messages\Models\Message $message
     * @param Request                           $request
     * @return JsonResponse
     */
    public function updateMarkReadMessage(Message $message, Request $request)
    {
        $successMessage = __('Operation Successful');
        $errorMessage   = __('An error has occurred');

        try {

            DB::beginTransaction();

            $user    = Auth::user();
            $userId   = $user->usr_id;

            $this->validateMessagesByUserService->execute($message, $userId);

            $message->message_read      = 1;
            $message->message_date_read = Carbon::now();
            $message->save();

            DB::commit();

            $data[ 'message' ] = new MessageResource($message);

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
     *   path="/messages/{message_id}/mark-delete-message",
     *   summary="Update record mark delete on messages ",
     *   tags={"Message"},
     *   consumes={"application/x-www-form-urlencoded"},
     *   @SWG\Parameter(
     *     name="message_id",
     *     in="path",
     *     description="Message ID",
     *     required=true,
     *     type="string"
     *   ),
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array",
     *                                        @SWG\Items(ref="#/definitions/Messages")),
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
     * @param Message $message
     * @param Request $request
     * @return JsonResponse
     */
    public function updateMarkDeleteMessage(Message $message, Request $request)
    {
        $successMessage = __('Operation Successful');
        $errorMessage   = __('An error has occurred');

        try {
            DB::beginTransaction();

            $user    = Auth::user();
            $userId   = $user->usr_id;

            $this->validateMessagesByUserService->execute($message, $userId);

            $message->message_deleted = 1;
            $message->save();

            DB::commit();

            $data[ 'message' ] = new MessageResource($message);

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
     * @SWG\Post(
     *   path="/messages/mark-delete-messages",
     *   summary="Update record mark delete on messages ",
     *   tags={"Message"},

     *   @SWG\Parameter(
     *     name="message_ids[]",
     *     in="query",
     *     description="Messages ID",
     *     required=true,
     *     type="array",
     *  collectionFormat="multi",
     *     @SWG\Items(
     *          type="integer",
     *          format="int64"
     *     )
     *   ),
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array",
     *                                        @SWG\Items(ref="#/definitions/Messages")),
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
     * @param Request $request
     * @return JsonResponse
     */
    public function updateMarkDeleteMessages(Request $request)
    {
        $successMessage = __('Operation Successful');
        $errorMessage   = __('An error has occurred');

        try {
            DB::beginTransaction();

            $user    = Auth::user();
            $userId   = $user->usr_id;

            $messages=$request->message_ids?? [];
            $messages=$this->updateMessagesByUserService->execute($messages, $userId);

            DB::commit();

            $data[ 'messages' ] = new MessageCollection($messages);

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
     *   path="/messages/reset-messages",
     *   summary="Reset Messages ",
     *   tags={"Message"},
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array",
     *                                        @SWG\Items(ref="#/definitions/Messages")),
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
     * @param Request $request
     * @return JsonResponse
     */
    public function resetMessages()
    {

        $successMessage = __('Operation Successful');
        $errorMessage   = __('An error has occurred');

        try {
            DB::beginTransaction();

            $user     = Auth::user();
            $userId   = $user->usr_id;


            $messages=Message::where('usr_id',$userId)
                ->update([
                    'usr_id'=>$userId,
                    'message_read'=>0,
                    'message_deleted'=>0,
	                'final_date'=> Carbon::now()->addDay()
                ]);


            return $this->successResponseWithMessage([], $successMessage);

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
     *   path="/messages/categories",
     *   summary="List Messages by category ",
     *   tags={"Message"},
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
     *     name="category",
     *     in="query",
     *     description="Category Id. 1->Promo , 2->Internal",
     *     type="integer",
     *     default=1
     *   ),
     *   security={
     *     {"password": {}, "user_ip":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array",
     *                                        @SWG\Items(ref="#/definitions/Messages")),
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
    public function listMessagesByCategory(Request $request)
    {
        $category=$request->input('category');
        $user     = Auth::user();
        $userId   = $user->usr_id;
        $messages = Message::where('message_deleted', '=', 0)
            ->where('message_type', '<>', 1)
            ->where('usr_id', $userId)
            ->latest('message_date_received')
            ->whereHas('batch',function($query)use ($category){
                if($category==MessageTemplateCategory::PROMO) {
                    $query->whereDate('final_date','=','0000-00-00')
                    ->orwhereDate('final_date', '>=', Carbon::now());

                }
            })
            ->whereHas('batch.messageTemplate.messageTemplateCategory',function($query) use ($category){
                $query->where('category_id',$category);
            })
            ->paginateByRequest();

        $messageCollection = new MessageCollection($messages);

        $data[ 'messages' ] = $messageCollection;

        return $this->successResponseWithMessage($data);
    }

    /**
     * @SWG\Put(
     *   path="/messages/mark-read-messages",
     *   summary="Update record mark read on multiple messages ",
     *   consumes={"application/json"},
     *   tags={"Message"},
     *   @SWG\Parameter(
     *     name="message_ids",
     *     in="body",
     *     description="Message IDs",
     *     required=true,
     *     @SWG\Schema(
     *         @SWG\Property(property="message_ids", type="array",
     *           @SWG\Items(
     *          type="integer",
     *          format="int64"
     *     )),
     *     )
     *   ),
     *   security={
     *     {"password": {}, "user_ip":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array",
     *                                        @SWG\Items(ref="#/definitions/Messages")),
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
     * @param Request $request
     * @return JsonResponse
     */
    public function updateMarkReadMessages(Request $request)
    {
        $successMessage = __('Successful');
        $errorMessage   = __('An error has ocurred');

        try {
            DB::beginTransaction();

            $messages=$request->input('message_ids')?? [];
            $messages=$this->updateReadMessagesByUserService->execute($messages);

            DB::commit();

            $data[ 'messages' ] = new MessageCollection($messages);

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
