<?php

namespace App\Core\Auth\Controllers;

use App\Core\Clients\Models\Client;
use App\Core\Base\Classes\ModelConst;
use App\Http\Controllers\ApiController;
use App\Core\Users\Notifications\PasswordToken;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use App\Core\Users\Models\User;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends ApiController
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('client.credentials');

    }

    /**
     * @SWG\Post(
     *   path="/password/recovery",
     *   summary="Get reset token",
     *   tags={"Password"},
     *   consumes={"multipart/form-data"},
     *   @SWG\Parameter(
     *     name="email",
     *     in="formData",
     *     description="User email",
     *     type="string",
     *     required=true,
     *   ),
     *   security={{"client_credentials": {}, "user_ip":{}}},
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(
     *          property="data",
     *          type="array",
     *          @SWG\Items(
     *              @SWG\Property(property="token", type="string", description="Reset Token", example="NVP5URva5iw-UMWEA3m825hAk8lAYPq5QZoEKCJa"),
     *          )
     *       ),
     *     ),
     *   ),
     *   @SWG\Response(response=400, ref="#/responses/400"),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function getResetToken(Request $request) {
        $this->validate($request, ['email' => 'required|email|exists:mysql_external.users,usr_email']);
        $client = Client::where('id', $request['oauth_client_id'])->first();
        $site = $client->site ? $client->site : null;

        $system = $site ? $site->system : null;
        $user = User::where( 'usr_email', '=', $request->email )
            ->where( 'sys_id', '=', $system->sys_id )
            ->where( 'usr_active', ModelConst::ENABLED )
            ->first()
        ;
        if (!$user) {
            return $this->errorResponse(trans('passwords.user'),400);
        }
        $token = hash_hmac('sha256', Str::random(40), $request->email);
        DB::connection('mysql')->table('password_resets')->where('email', $request->email)->delete();
        DB::connection('mysql')->table('password_resets')->insert(['email' => $request->email, 'token' => $token, 'created_at' => date("Y-m-d H:i:s")]);

        retry(5, function() use ($user, $token, $site) {
            $client_url = $site->site_url_https.'?token='.$token;
            if($site->site_id == 1){ //el de trillonario es diferente (quitarlo cuando salga trillonario!)
                $client_url = $site->site_url_https.'/password_confirmation.php?token='.$token;
            }

            Mail::to($user->usr_email)
                ->send(new PasswordToken( $user, $client_url));
        }, 100);
        return $this->successResponse(['data'=> ['token' => $token]],200);
    }

    /**
     * @SWG\Post(
     *   path="/password/recovery/confirm",
     *   summary="Change password",
     *   tags={"Password"},
     *   consumes={"multipart/form-data"},
     *   @SWG\Parameter(
     *     name="token",
     *     in="formData",
     *     description="Token",
     *     type="string",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="password",
     *     in="formData",
     *     description="New password",
     *     type="string",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="password_confirmation",
     *     in="formData",
     *     description="New password confirmation",
     *     type="string",
     *     required=true,
     *   ),
     *   security={{"client_credentials": {}, "user_ip":{},
     *   }},
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(
     *          property="data",
     *          type="string",
     *          description="Successful message",
     *          example="Password changed successfully.",
     *       ),
     *     ),
     *   ),
     *   @SWG\Response(response=400, ref="#/responses/400"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function change(Request $request) {
        $rules = [
            'token' => 'required',
            'password' => 'required|confirmed|min:6',
        ];
        $this->validate($request, $rules);

        $token_exists = DB::connection('mysql')->table('password_resets')->where('token', $request->token)->exists();
        $record = DB::connection('mysql')->table('password_resets')->where('token', $request->token)->first();

        if($token_exists) {
            //Poner sys_id cuando incluya en clients
            $client = Client::where('id', $request['oauth_client_id'])->first();
            $site = $client->site ? $client->site : null;
            $user = User::where([['usr_email', $record->email],['sys_id', $site->sys_id]] )->first();

            if($user->usr_password !== $request->password) {
                $user->usr_password = $request->password;
                $user->save();
                $this->broker()->deleteToken($user);
            }else {
                return $this->errorResponse(trans('lang.different_password'),422);
            }
            return $this->showMessage(trans('lang.password_changed'));
        }else {
            return $this->errorResponse(trans('passwords.token'),400);
        }
    }
}
