<?php

namespace App\Core\Clients\Controllers;

use App\Core\Base\Classes\ModelConst;
use App\Core\Rapi\Requests\SendContactRequest;
use App\Core\Users\Notifications\ContactNotification;
use App\Core\Users\Models\User;
use App\Core\Base\Traits\ApiResponser;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Swagger\Annotations as SWG;

/**
 * Class ContactController
 * @package App\Http\Controllers
 */
class ContactController extends Controller
{
    use ApiResponser;

    public function __construct()
    {
    }


    /**
     * @SWG\Post(
     *   path="/contact",
     *   summary="Store contact ",
     *   tags={"Contact"},
     *   consumes={"multipart/form-data"},
     *   @SWG\Parameter(
     *     name="name",
     *     in="formData",
     *     description="Name",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="last_name",
     *     in="formData",
     *     description="last name",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="email",
     *     in="formData",
     *     description="email",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="notes",
     *     in="formData",
     *     description="notes",
     *     required=true,
     *     type="string"
     *   ),
     *   security={
     *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(response=200, ref="#/responses/200"),
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
    public function store(SendContactRequest $request)
    {
        $successMessage = __('Operation Successful');
        $errorMessage   = __('An error has occurred');

        $locale = app()->getLocale();

        $email = '';
        if($locale === ModelConst::LOCALE_LANGUAGE_ENGLISH){
            $email = 'support@wintrillions.com';
        }
        if($locale === ModelConst::LOCALE_LANGUAGE_SPANISH){
            $email = 'clientes@trillonario.com';
        }
        if($locale === ModelConst::LOCALE_LANGUAGE_PORTUGUES){
            $email = 'suporte@trillonario.com';
        }

        $invitedUser = new User();
        $invitedUser->usr_email = $email;
        $dataRequest = (object)$request->all();
        $dataRequest->host = request()->headers->get('host');
        Notification::route('mail', $email)
            ->notify(new ContactNotification($locale, $dataRequest));

        $data[ 'contact' ] = __('Successful');

        return $this->successResponseWithMessage($data, $successMessage, Response::HTTP_CREATED);

    }
}
