<?php

namespace App\Core\Users\Controllers\LiveFeed;

use App\Http\Controllers\ApiController;
//use App\Transformers\LiveFeed\LiveFeedUserTransformer;
use App\Core\Clients\Models\Client;
use App\Core\Users\Models\User;
use App\Core\Rapi\Models\Promotion;

use App\Http\Controllers\LiveFeed\PromotionTransformer;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Carbon;


class LiveFeedController extends ApiController {

    public function __construct() {
        parent::__construct();
        $this->middleware('client.credentials');
    }


    /**
     * @SWG\Get(
     *   path="/live_feed/userdetails/{usr_id}",
     *   summary="Get user details",
     *   tags={"LiveFeed"},
     *   @SWG\Parameter(
     *     name="usr_id",
     *     in="path",
     *     description="User id",
     *     required=true,
     *     type="integer"
     *   ),
     *   security={{"client_credentials": {}, "user_ip":{}}},
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *          @SWG\Property(
     *              property="address",
     *              type="string",
     *              example="Ratatouille avenue, 34B"
     *          ),
     *          @SWG\Property(
     *              property="birth_date",
     *              type="string",
     *              example="1987-05-21"
     *          ),
     *          @SWG\Property(
     *              property="city",
     *              type="string",
     *              example="Paris"
     *          ),
     *          @SWG\Property(
     *              property="country",
     *              type="string",
     *              example="MT"
     *          ),
     *          @SWG\Property(
     *              property="currency",
     *              type="string",
     *              example="USD"
     *          ),
     *          @SWG\Property(
     *              property="email",
     *              type="string",
     *              example="tony@example.com"
     *          ),
     *          @SWG\Property(
     *              property="first_name",
     *              type="string",
     *              example="Tony"
     *          ),
     *          @SWG\Property(
     *              property="is_blocked",
     *              type="boolean",
     *              example="false"
     *          ),
     *          @SWG\Property(
     *              property="is_excluded",
     *              type="boolean",
     *              example="true"
     *          ),
     *          @SWG\Property(
     *              property="language",
     *              type="string",
     *              example="es-la"
     *          ),
     *          @SWG\Property(
     *              property="last_name",
     *              type="string",
     *              example="Carrot"
     *          ),
     *          @SWG\Property(
     *              property="mobile",
     *              type="string",
     *              example="123456"
     *          ),
     *          @SWG\Property(
     *              property="mobile_prefix",
     *              type="string",
     *              example="256"
     *          ),
     *          @SWG\Property(
     *              property="origin",
     *              type="string",
     *              example="sub.example.com"
     *          ),
     *          @SWG\Property(
     *              property="postal_code",
     *              type="string",
     *              example="70000"
     *          ),
     *          @SWG\Property(
     *              property="roles",
     *              type="array",
     *              @SWG\Items(),
     *              example="['VIP+', 'INTERNAL_ACCOUNT']"
     *          ),
     *          @SWG\Property(
     *              property="sex",
     *              type="string",
     *              example="Female"
     *          ),
     *          @SWG\Property(
     *              property="title",
     *              type="string",
     *              example="Dr"
     *          ),
     *          @SWG\Property(
     *              property="user_id",
     *              type="string",
     *              example="2345678"
     *          ),
     *          @SWG\Property(
     *              property="username",
     *              type="string",
     *              example="PirateTony34"
     *          ),
     *          @SWG\Property(
     *              property="verified_at",
     *              type="string",
     *              example="2020-06-01 10:30:30"
     *          ),
     *          @SWG\Property(
     *              property="registration_code",
     *              type="string",
     *              example="AVR5642"
     *          ),
     *          @SWG\Property(
     *              property="registration_date",
     *              type="string",
     *              example="2019-03-24 10:52:27"
     *          ),
     *          @SWG\Property(
     *              property="affiliate_reference",
     *              type="string",
     *              example="AFF_124_UK"
     *          ),
     *          @SWG\Property(
     *              property="market",
     *              type="string",
     *              example="es"
     *          ),
     *          @SWG\Property(
     *              property="segmentation",
     *              type="array",
     *              @SWG\Items(),
     *              example="[]"
     *          ),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=405, ref="#/responses/405"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     */
    public function userDetails($usr_id) {

        $user = User::find($usr_id);
        if (!$user) {
            return $this->errorResponse('User nor found', 404);
        }

        $is_excluded = false;
        $conn = DB::connection('mysql_external');
        $optin = collect($conn->select("select * from optin_exclusions where usr_id = :id", ['id' => $usr_id]));
        if ($optin->isNotEmpty()) {
            $optin = get_object_vars($optin->first());
            $is_excluded = $optin['deleted'] ? false : true;

        }

        $registration_code = "";
        $roles = array();
        if ($user->usr_vip_level != null || $user->usr_vip_level != '') {
            array_push($roles, $user->usr_vip_level);
        }
        if ($user->usr_internal_account == 1) {
            array_push($roles, 'INTERNAL_ACCOUNT');
        }

        $birthdate = $user->usr_birthdate == '0000-00-00' ? "" :(string)$user->usr_birthdate;
        if ($user->usr_verification == 0) {
            $verified_at_str = null;
        } else {
            $verified_at = $user->usr_verification_date == '0000-00-00 00:00:00' ? null : \DateTime::createFromFormat("Y-m-d H:i:s", $user->usr_verification_date);
            if ($verified_at != null) {
                $verified_at_str = str_replace('+00:00', 'Z', $verified_at->format(\DateTime::RFC3339));
            } else {
                $verified_at_str = null;
            }
        }

        $registration_date = \DateTime::createFromFormat("Y-m-d H:i:s", $user->usr_regdate);

        $country_iso =  ($user->country_attributes != "") ? (string)$user->country_attributes['iso'] : "";

        $segmentation = null;

        $data = [
            'address' => (string)$user->usr_address1,
            'birth_date' => $birthdate,
            'city' => (string)$user->usr_city,
            'country' => $country_iso,
            'currency' => (string)$user->curr_code,
            'email' => (string)$user->usr_email,
            'first_name' => (string)$user->usr_name,
            'is_blocked' => (boolean)!$user->usr_active,
            'is_excluded' => $is_excluded,
            'language' => (string)$user->usr_language,
            'last_name' => (string)$user->usr_lastname,
            'mobile' => (string)$user->usr_mobile,
            'mobile_prefix' => "",
            'origin' => $user->site_attributes['url_ssl'],
            'postal_code' => (string)$user->usr_zipcode,
            "roles" => $roles,
            "sex" => (string) $user->title['gender'] == '#MALE#' ? 'Male' : 'Female',
            "title" => (string) $user->title['name'],
            "user_id" => (string) $user->usr_id,
            'username' => (string)$user->usr_email,
            "verified_at" => $verified_at_str,
            "registration_code" => $registration_code,
            "registration_date" => str_replace('+00:00', 'Z', $registration_date->format(\DateTime::RFC3339)),
            "affiliate_reference" => (string)$user->usr_cookies,
            "market" => "",
            "segmentation" => $segmentation
        ];

        //$data = $this->encode_array($data);

        return $this->successResponse($data, 200);


        //return response()->json($data, 200, ['content-type' => 'application/json', 'cache-control' => 'no-cache']);
    }

    /**
     * @SWG\Get(
     *   path="/live_feed/userconsents/{usr_id}",
     *   summary="Get user consents",
     *   tags={"LiveFeed"},
     *   @SWG\Parameter(
     *     name="usr_id",
     *     in="path",
     *     description="User id",
     *     required=true,
     *     type="integer"
     *   ),
     *   security={{"client_credentials": {}, "user_ip":{}}},
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *          @SWG\Property(
     *              property="consents",
     *              type="array",
     *              @SWG\Items(
     *                  @SWG\Schema(
     *                      @SWG\Property(
     *                          property="opted_in",
     *                          type="boolean",
     *                          example="true",
     *                      ),
     *                      @SWG\Property(
     *                          property="type",
     *                          type="string",
     *                          example="Email",
     *                      )
     *                  ),
     *              ),
     *          ),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=405, ref="#/responses/405"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     */
    public function userConsents($usr_id) {

        $user = User::find($usr_id);
        if (!$user) {
            return $this->errorResponse('User nor found', 404);
        }

        $optin_email = false;
        $optin_sms = false;
        $optin_telephone = false;
        $optin_postMail = false;
        $optin_siteNotifications = false;
        $optin_pushNotifications = false;

        $allways_true = false;

        $conn = DB::connection('mysql_external');
        $optin = collect($conn->select("select * from optin_email where usr_id = :id", ['id' => $usr_id]));
        if ($optin->isNotEmpty()) {
            $optin = get_object_vars($optin->first());
            $optin_email = $optin['deleted'] ? false : true;
        }

        $optin = collect($conn->select("select * from optin_sms where usr_id = :id", ['id' => $usr_id]));
        if ($optin->isNotEmpty()) {
            $optin = get_object_vars($optin->first());
            $optin_sms = $optin['deleted'] ? false : true;
        }

        $optin = collect($conn->select("select * from optin_phone where usr_id = :id", ['id' => $usr_id]));
        if ($optin->isNotEmpty()) {
            $optin = get_object_vars($optin->first());
            $optin_telephone = $optin['deleted'] ? false : true;
        }


        $optin = collect($conn->select("select * from optin_direct_mail where usr_id = :id", ['id' => $usr_id]));
        if ($optin->isNotEmpty()) {
            $optin = get_object_vars($optin->first());
            $optin_postMail = $optin['deleted'] ? false : true;
        }

        $optin = collect($conn->select("select * from optin_push where usr_id = :id", ['id' => $usr_id]));
        if ($optin->isNotEmpty()) {
            $optin = get_object_vars($optin->first());
            $optin_pushNotifications = $optin['deleted'] ? false : true;
        }

        $optin_siteNotifications = $optin_pushNotifications;


        if ($allways_true) {
            $optin_email = true;
            $optin_sms = true;
            $optin_telephone = true;
            $optin_postMail = true;
            $optin_siteNotifications = true;
            $optin_pushNotifications = true;
        }

        $response = array(
            "consents" => array(
                array(
                    "opted_in" => $optin_email,
                    "type" => "Email"
                ),
                array(
                    "opted_in" => $optin_sms,
                    "type" => "SMS"
                ),
                array(
                    "opted_in" => $optin_telephone,
                    "type" => "Telephone"
                ),
                array(
                    "opted_in" => $optin_postMail,
                    "type" => "PostMail"
                ),
                array(
                    "opted_in" => $optin_siteNotifications,
                    "type" => "SiteNotification"
                ),
                array(
                    "opted_in" => $optin_pushNotifications,
                    "type" => "PushNotification"
                )
            )
        );

        return response()->json($response, 200, ['content-type' => 'application/json', 'cache-control' => 'no-cache']);

    }


    /**
     * @SWG\Post(
     *   path="/live_feed/userconsents/{usr_id}",
     *   summary="Set user consents",
     *   tags={"LiveFeed"},
     *   @SWG\Parameter(
     *     name="usr_id",
     *     in="path",
     *     description="User id",
     *     required=true,
     *     type="integer"
     *   ),
     *   security={{"client_credentials": {}, "user_ip":{}}},
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation"
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=405, ref="#/responses/405"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     */
    public function setUserConsents(Request $request, $usr_id) {

        $consents = $request->all()['consents'];
        $user = User::find($usr_id);
        if (!$user) {
            return $this->errorResponse('User nor found', 404);
        }

        foreach ($consents as $consent) {
            if ($consent['type'] == "Email") {
                if ($consent['opted_in'] == true){
                    $user->usr_NoPromoemails = 0;
                    $user->usr_NoPromoemails_date = null;
                } else {
                    $user->usr_NoPromoemails = 1;
                    $user->usr_NoPromoemails_date = Carbon::now();
                }
            } elseif ($consent['type'] == "Telephone") {
                if ($consent['opted_in'] == true){
                    $user->usr_notTelemCall = 0;
                } else {
                    $user->usr_notTelemCall = 1;
                }
            } elseif ($consent['type'] == "SMS") {
                $value = $consent['opted_in'] ? 1 : 0;
                $this->_setUserConsents('optin_sms', $user->usr_id, $value);
            } elseif ($consent['type'] == "PostMail") {
                $value = $consent['opted_in'] ? 1 : 0;
                $this->_setUserConsents('optin_direct_mail', $user->usr_id, $value);
            } elseif ($consent['type'] == "SiteNotification" || $consent['type'] == "PushNotification") {
                $value = $consent['opted_in'] ? 1 : 0;
                $this->_setUserConsents('optin_push', $user->usr_id, $value);
            }

        }

        if ($user->isDirty()) {
            $user->save();
        }
        return $this->successResponse(['data' => 'Success'], 200);

    }

    private function _setUserConsents($table, $usr_id, $value) {

        $conn = DB::connection('mysql_external');
        $optin = collect($conn->select("select * from {$table} where usr_id = :id", ['id' => $usr_id]));
        $created_at = $update_at = $delete_at = Carbon::now();
        if ($value == 1) {
            $deleted = 0;
        } else {
            $deleted = 1;
        }
        if ($optin->isNotEmpty()) {
            $optin = get_object_vars($optin->first());
            $change = ($optin['deleted'] != $deleted);
            if ($change) {
                $conn->update("UPDATE {$table}  t SET t.`updated_at` = :updated_at, t.`deleted_at` = :deleted_at, t.`deleted` = :del WHERE  :id",
                    ['id' => $usr_id, 'updated_at' => $update_at, 'deleted_at' => $delete_at, 'del' => $deleted]);
            }
        } else {
            $change = 1;
            if ($value == 1) {
                $conn->insert("INSERT INTO `{$table}` (`usr_id`, `created_at`, `updated_at`) VALUES (:id, :created_at, :updated_at)",
                    ['id' => $usr_id, 'created_at' => $created_at, 'updated_at' => $update_at]);
            } else {
                $conn->insert("INSERT INTO `{$table}` (`usr_id`, `created_at`, `updated_at`, `deleted_at`, `deleted`)
                                                                      VALUES (:id, :created_at, :updated_at, :deleted_at, :del)",
                    ['id' => $usr_id, 'created_at' => $created_at, 'updated_at' => $update_at, 'deleted_at' => $delete_at, 'del' => $deleted]);
            }
        }

        return $change;
    }


    /**
     * @SWG\Get(
     *   path="/live_feed/userblocks/{usr_id}",
     *   summary="Get user blocks",
     *   tags={"LiveFeed"},
     *   @SWG\Parameter(
     *     name="usr_id",
     *     in="path",
     *     description="User id",
     *     required=true,
     *     type="integer"
     *   ),
     *   security={{"client_credentials": {}, "user_ip":{}}},
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=405, ref="#/responses/405"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     */
    public function userBlocks($usr_id) {

        $user = User::find($usr_id);
        if (!$user) {
            return $this->errorResponse('User nor found', 404);
        }

        $is_excluded = false;
        $conn = DB::connection('mysql_external');
        $optin = collect($conn->select("select * from optin_exclusions where usr_id = :id", ['id' => $usr_id]));
        if ($optin->isNotEmpty()) {
            $optin = get_object_vars($optin->first());
            $is_excluded = $optin['deleted'] ? false : true;

        }

        $response = array(
            "blocks" => array(
                array(
                    "active" => $is_excluded,
                    "type" => "Excluded",
                    "note" => ""
                ),
                array(
                    "active" => (boolean)!$user->usr_active,
                    "type" => "Blocked",
                    "note" => ""
                )
            )
        );

        return response()->json($response, 200, ['content-type' => 'application/json', 'cache-control' => 'no-cache']);

    }


    /**
     * @SWG\Get(
     *   path="/live_feed/bonus",
     *   summary="Active bonus list",
     *   tags={"LiveFeed"},
     *   consumes={"multipart/form-data"},
     *   security={{"client_credentials": {}, "user_ip":{}}},
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(
     *          property="data",
     *          type="array",
     *          @SWG\Items(
     *              @SWG\Property(
     *                  property="text",
     *                  type="string",
     *                  example="ZZY77X9Q: Free Ticket Irish Lotto (New Acquisition Strategy / Follow-up Funnel)",
     *              ),
     *              @SWG\Property(
     *                  property="value",
     *                  type="string",
     *                  example="ZZY77X9Q",
     *              ),
     *          ),
     *       ),
     *       @SWG\Property(property="Success", type="boolean", example="true"),
     *       @SWG\Property(property="Errors", type="array", @SWG\Items(), example="[]"),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=405, ref="#/responses/405"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function bonusList() {
        $promotion = Promotion::where('expiration_date','>',date("Y-m-d"))
            ->where('promo_category', '=', 'admin')
            ->whereNull('usr_id')
            ->where('promo_usr_id', '=', "")
            ->orderBy('code', 'desc')
            ->get();
        if($promotion)
        {
            $promotion->transformer = PromotionTransformer::class;
        }
        if(!$promotion)
            return $this->errorResponse(trans('lang.no_data'), 422);
        $response = collect([]);
        $promotion->each(function ($item) use ($response) {
            $response->push(['text' => $item->code.": ".utf8_encode($item->name), 'value' => $item->code ]);
        });

        $response = $this->encode_array($response);

        return response()->json([
            'Data' => $response,
            'Success' => true,
            'Error' => []]);
    }
    /**
     * @SWG\Post(
     *   path="/live_feed/bonus/credit",
     *   summary="Credit bonus",
     *   tags={"LiveFeed"},
     *   consumes={"multipart/form-data"},
     *   @SWG\Parameter(
     *     name="user_id",
     *     in="formData",
     *     description="User Id",
     *     type="integer",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="bonus_code",
     *     in="formData",
     *     description="Bonus code",
     *     type="string",
     *     required=true,
     *   ),
     *   security={{"client_credentials": {}, "user_ip":{}}},
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(property="data", type="string", example="Success"),
     *       @SWG\Property(property="code", description="Status Code", example="200"),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=405, ref="#/responses/405"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function bonusCredit(Request $request) {
        $request->validate([
            "user_id" => "required",
            "bonus_code" => "required"
        ]);
        $user_id = request('user_id') ? request('user_id') : 0;
        $bonus_code = request('bonus_code') ? request('bonus_code') : 0;
        $promotion = Promotion::where('code', '=' ,$bonus_code)
            ->first();
        if($promotion)
        {
            $promotion->transformer = PromotionTransformer::class;
        }
        if(!$promotion)
            return $this->errorResponse(trans('lang.invalid_promocode'), 422);
        $creation_date = Carbon::now();
        $conn = DB::connection('mysql_external');
        $table = 'promotions_requests';
        try {
            $conn->insert("INSERT INTO `{$table}` (`promotion_id`, `usr_id` ,`creation_date`) VALUES (:promotion_id, :usr_id, :creation_date)",
                ['promotion_id' => $promotion->promotion_id ,'usr_id' => $user_id, 'creation_date' => $creation_date]);
        } catch (\Exception $ex){
            if ($ex->getCode() == 23000) {
                return $this->successResponse(['data' => 'Success'], 200);
                //return $this->errorResponse('The bonus has already been assigned to the user', 422);
            }
            return $this->successResponse(['data' => 'Success'], 200);
            //return $this->errorResponse('Error setting the bonus to the user', 422);
        }

        return $this->successResponse(['data' => 'Success'], 200);
    }

    public function bonusCreditFunds(Request $request) {
        //not implemented
    }
}
