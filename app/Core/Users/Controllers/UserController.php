<?php

namespace App\Core\Users\Controllers;

use App\Core\Clients\Models\Client;
use App\Core\Clients\Models\ClientProductCountryBlacklist;
use App\Core\Base\Classes\ModelConst;
use App\Core\Base\Services\SetTransformerService;
use App\Core\Base\Services\TranslateArrayService;
use App\Core\Base\Traits\CacheRedisTraits;
use App\Core\Countries\Models\Country;
use App\Core\Countries\Models\CountryRegion;
use App\Core\Users\Models\Currency;
use App\Core\Users\Requests\Rules\CheckSpamPasswordPeriodUserRule;
use App\Core\Users\Requests\XmlUserStoreRequest;
use App\Core\Clients\Services\IP2LocTrillonario;
use App\Core\Lotteries\Models\LiveLotterySubscription;
use App\Core\Rapi\Models\Pixel;
use App\Core\Rapi\Models\ProductType;
use App\Core\Raffles\Models\RaffleSubscription;
use App\Core\FreeSpin\Services\ApplyBonusFreeSpinService;
use App\Core\Base\Services\ClientService;
use App\Core\Base\Services\DirtyExceptionRedirectUkGbService;
use App\Core\Base\Services\FastTrackLogService;
use App\Core\Base\Services\GetAllValuesFromHeaderService;
use App\Core\Base\Services\GetInfoFromExceptionService;
use App\Core\Base\Services\GetOriginRequestService;
use App\Core\Base\Services\SendLogConsoleService;
use App\Core\Users\Services\GetPixelByUserService;
use App\Core\Users\Services\StoreUserService;
use App\Core\Users\Services\ValidationCountyWithStateService;
use App\Core\Rapi\Models\Site;
use App\Core\Syndicates\Models\SyndicatePrize;
use App\Core\Syndicates\Models\SyndicateRafflePrize;
use App\Core\Rapi\Models\Ticket;
use App\Core\Base\Traits\Encoding;
use App\Core\Base\Traits\LogCache;
use App\Core\Base\Traits\ErrorNotificationTrait;
use App\Core\Base\Traits\Pixels;
use App\Core\Base\Traits\Utils;
use App\Core\Rapi\Transforms\PriceLineBoostedTransformer;
use App\Core\Users\Transforms\UserExtraDetailsTransformer;
use App\Core\Users\Transforms\UserWinningsLiveLotteryTransformer;
use App\Core\Users\Transforms\UserWinningsLotteryTransformer;
use App\Core\Users\Transforms\UserWinningsRaffleTransformer;
use App\Core\Users\Transforms\UserWinningsSyndicateRaffleTransformer;
use App\Core\Users\Transforms\UserWinningsSyndicateTransformer;
use App\Core\Users\Models\User;
use App\Core\Users\Models\UserDataChangesLog;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Core\Users\Transforms\UserTransformer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use GuzzleHttp\Client as ClientHttp;
use Swagger\Annotations as SWG;

class UserController extends ApiController
{
    use Utils;
    use LogCache;
    use ErrorNotificationTrait;
    use Pixels;
    use Encoding;
    use CacheRedisTraits;

    /** @var \App\Core\Users\Services\StoreUserService */
    private $storeUserService;
    /**
     * @var ValidationCountyWithStateService
     */
    private $validationCountyWithStateService;
    /**
     * @var ApplyBonusFreeSpinService
     */
    private $applyBonusFreeSpinService;
    /**
     * @var DirtyExceptionRedirectUkGbService
     */
    private $dirtyExceptionRedirectUkGbService;

    public function __construct(
        StoreUserService $storeUserService,
        ValidationCountyWithStateService $validationCountyWithStateService,
        ApplyBonusFreeSpinService $applyBonusFreeSpinService,
        DirtyExceptionRedirectUkGbService $dirtyExceptionRedirectUkGbService
    ) {
        parent::__construct();
        $this->middleware('check.ip')->except('optin', 'clear_cache');
        $this->middleware('auth:api')->except('store', 'xmlregister', 'optin', 'language',
            'validate_email', 'ip_who_is', 'index_pixels', 'clear_cache', 'create', 'testIp2Lock');
        $this->middleware('client.credentials')->only('store', 'xmlregister', 'optin', 'language', 'validate_email', 'ip_who_is', 'index_pixels', 'create');
        $this->middleware('transform.input:' . UserTransformer::class);//->only(['update_me', 'create']);

        /* Provide services */
        $this->storeUserService = $storeUserService;
        $this->validationCountyWithStateService = $validationCountyWithStateService;
        $this->applyBonusFreeSpinService = $applyBonusFreeSpinService;
        $this->dirtyExceptionRedirectUkGbService = $dirtyExceptionRedirectUkGbService;
    }

    /**
     * @SWG\Post(
     *   path="/users/xmlregister",
     *   summary="Register user using the xmlapi",
     *   tags={"Users"},
     *   consumes={"multipart/form-data"},
     *   @SWG\Parameter(
     *     name="name",
     *     in="formData",
     *     description="User name",
     *     type="string",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="last_name",
     *     in="formData",
     *     description="User last name",
     *     type="string",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="password",
     *     in="formData",
     *     description="User password",
     *     type="string",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="email",
     *     in="formData",
     *     description="User Email",
     *     type="string",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="country",
     *     in="formData",
     *     description="Country Id",
     *     type="integer",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="usr_state",
     *     in="formData",
     *     description="State Id or Name",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="phone",
     *     in="formData",
     *     description="User phone",
     *     type="string",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="language",
     *     in="formData",
     *     description="User preferred language",
     *     type="string",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="site_id",
     *     in="formData",
     *     description="Site id",
     *     type="integer",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="source",
     *     in="formData",
     *     description="Landing Source. Ex:FreePlay",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="utm_source",
     *     in="formData",
     *     description="Utm source",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="utm_campaign",
     *     in="formData",
     *     description="Utm campaign",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="utm_medium",
     *     in="formData",
     *     description="Utm medium",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="utm_content",
     *     in="formData",
     *     description="Utm content",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="utm_term",
     *     in="formData",
     *     description="Utm term",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="cookies",
     *     in="formData",
     *     description="Cookies",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="track",
     *     in="formData",
     *     description="Track",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="cookies_data1",
     *     in="formData",
     *     description="Cookies data1",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="cookies_data2",
     *     in="formData",
     *     description="Cookies data2",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="cookies_data3",
     *     in="formData",
     *     description="Cookies data3",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="cakecookie",
     *     in="formData",
     *     description="Cake cookie",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="freeproducts",
     *     in="formData",
     *     description="Type and price id to create a free order to the user. Ex: 2,62",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="getXMLCreated",
     *     in="formData",
     *     description="Using to get the xml created",
     *     type="integer",
     *     required=false,
     *   ),
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Response(
     *     response=201,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(property="data", allOf={ @SWG\Schema(ref="#/definitions/User"), }),
     *       @SWG\Property(property="code", description="Status Code", example="201"),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=405, ref="#/responses/405"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function xmlregister(XmlUserStoreRequest $request) {

        $this->encode($request);

        [$usr_state] = $this->validationCountyWithStateService->execute($request);

        $name = $request->usr_name;
        $email = $request->usr_email;
        $lastname = $request->usr_lastname ? $request->usr_lastname : '';
        $phone = $request->usr_phone;
        $pass = $request->usr_password;
        $lang = $request->usr_language ? $request->usr_language : $this->getLanguageCode();
        $_site_id = $request->site_id;
        $sys_id = $request->client_sys_id;
        $currcode = $request['country_currency'] ? $request['country_currency'] : 'USD';
        $source = $request->source ? $request->source : '';
        $utm_source = $request->utm_source ? $request->utm_source : '';
        $utm_campaign = $request->utm_campaign ? $request->utm_campaign : '';
        $utm_medium = $request->utm_medium ? $request->utm_medium : '';
        $utm_content = $request->utm_content ? $request->utm_content : '';
        $utm_term = $request->utm_term ? $request->utm_term : '';
        $country_id = $request->country_id;

        $getXMLCreated = $request->getXMLCreated ? $request->getXMLCreated : false;

        $usr_cookies_data4 = "";
        $usr_cookies_data5 = "";
        $usr_cookies_data6 = "";
        $trackcookie = "";
        $AffAccount = "";
        $cpaYES = 'no';
        $cookies = $request->usr_cookies ? $request->usr_cookies : '';
        if($cookies != ""){
            $aff_cookie = explode("_",$cookies);
            $AffAccount = $aff_cookie[0];
            if($AffAccount != ''){
                $trackcookie = $request->usr_track ? $request->usr_track : '';
                $usr_cookies_data4 = $request->usr_cookies_data4 ? $request->usr_cookies_data4 : '';
                $usr_cookies_data5 = $request->usr_cookies_data5 ? $request->usr_cookies_data5 : '';
                $usr_cookies_data6 = $request->usr_cookies_data6 ? $request->usr_cookies_data6 : '';
            }
            if($aff_cookie[1] == '6c636dd7' || $aff_cookie[1] == '7904e8ea'){
                //6c636dd7 = tri, 7904e8ea = ltk
                $cpaYES = 'yes';
            }
            else {
                $cpaYES = 'no';
            }
        }

        $ckecookie = request('cakecookie') ? request('cakecookie') : 0;
        $ip = $request->user_ip;

        $array_freeproduct = array();
        $freeproducts = $request->freeproducts ? $request->freeproducts : '';
        if($freeproducts != ""){
            $array_freeproduct = explode(',', $freeproducts);
        }

        //crear el xml
        $xml = "<SignUp>
				<Email>".$email."</Email>
				<Name><![CDATA[".$name."]]></Name>
				<LastName>".$lastname."</LastName>
				<Phone><![CDATA[".$phone."]]></Phone>
				<Password>".$pass."</Password>
				<Lang>".$lang."</Lang>
				<SiteId>".$_site_id."</SiteId>
				<CurrCode>".$currcode."</CurrCode>
				<Source>".$source."</Source>
				<Country>".$country_id."</Country>
				<State>".$usr_state."</State>
				<utm_source>".$utm_source."</utm_source>
				<utm_campaign>".$utm_campaign."</utm_campaign>
				<utm_medium>".$utm_medium."</utm_medium>
				<utm_content>".$utm_content."</utm_content>
				<utm_term>".$utm_term."</utm_term>
				<AffAccount>".$AffAccount."</AffAccount>
				<AffData4>".$usr_cookies_data4."</AffData4>
				<AffData5>".$usr_cookies_data5."</AffData5>
				<AffData6>".$usr_cookies_data6."</AffData6>
				<AffTrack>".$trackcookie."</AffTrack>
				<AffCpa>".$cpaYES."</AffCpa>
				<IP>".$ip."</IP>
				<AffCke>".$ckecookie."</AffCke>";
        if($freeproducts != ""){
            $xml .= "<OrderFree>
                        <OrderIP>".$ip."</OrderIP>
                        <Product>
                            <ProductType>$array_freeproduct[0]</ProductType>
                            <ProductId>$array_freeproduct[1]</ProductId>
                        </Product>
                    </OrderFree>";
        }
        $xml .= "</SignUp>";

        $xmlcontent = utf8_encode('<?xml version="1.0" encoding="UTF-8"?><Envelope><Body>'.$xml.'</Body></Envelope>');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch , CURLOPT_SSL_VERIFYPEER , false );
        curl_setopt($ch , CURLOPT_SSL_VERIFYHOST , false );

        $apikey = "11irt11"; //apikey de PRODUCCION
        $totest = false;
        if (env('APP_ENV', null) == 'dev'){
            $totest = true;
            $apikey = "1111"; //apikey de TEST
        } elseif (env('APP_ENV', null) == 'stage') {
            $totest = true;
            $apikey = "1111"; //apikey de TEST
        } elseif (env('APP_ENV', null) == 'test') {
            $totest = true;
            $apikey = "1111"; //apikey de TEST
        }

        if ($totest){
            curl_setopt($ch, CURLOPT_URL,'http://apitest.trillonario.com/SignUp.php?ApiKey='.$apikey);
        }else{
            curl_setopt($ch, CURLOPT_URL,'https://xmlapi.trillonario.com/SignUp.php?ApiKey='.$apikey);
        }
        curl_setopt($ch, CURLOPT_POST, 1);
        $arrayToPass = array('xml' => $xmlcontent);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$arrayToPass);

        $content = curl_exec($ch);
        if(curl_errno($ch)){
            $error_user = 'APIERROR1';
            //$error_user = 'Error0';
            return $this->errorResponse($error_user.": ".var_dump(curl_errno($ch)), 422);
        }

        $data_xml = simplexml_load_string($content);

        if($getXMLCreated){
            echo "Request:";
            print_r($xmlcontent,false);
            echo "Response:";
            print_r($content,false);
        }

        $error_user = "";
        if($data_xml){
            foreach($data_xml as $k => $body){
                foreach($body as $k2 => $info_result){
                    $info = json_decode(json_encode($info_result), TRUE);
                    if($info['ResponseCode']=='OK'){
                        $user = null;
                        if(array_key_exists( 'UserId', $info)){
                            $idUser = $info['UserId'];
                            $user = User::query()->where('usr_id', $idUser)->first();
                            $user = GetPixelByUserService::execute($request, $user);
                            $users = collect([]);
                            $users = $users->push($user);
                            $setTransformerObjet = new SetTransformerService();
                            $users = $setTransformerObjet->execute($users);
                            $user = collect($users)->first();
                        }

                        $data = [
                            'data' => [
                                'result' => 'Success',
                                'm'      => $info[ 'SessionM' ],
                                'user'      => $user,
                            ],
                        ];

                        return $this->successResponse($data);
                    }else{
                        if($info['ResponseCode']=='5'){
                            $error_user = 'EMAILINVALID';
                        }elseif ($info['ResponseCode']=='13'){
                            $error_user = 'USER_ALREADY_REGISTERED'; //'EMAILEXISTS';
                        }elseif ($info['ResponseCode']=='15'){
                            $error_user = 'INVALIDPASSWORD'; //'INVALIDPASSWORD';
                        }else{
                            $error_user = 'APIERROR2: '.var_dump($info);
                            //$error_user = 'Error1';
                        }
                    }
                }
            }
        }else{
            $error_user = 'APIERROR3: '.var_dump($data_xml).' ';
            //$error_user = 'Error2';
        }

        return $this->errorResponse($error_user, 422);

    }

    /**
     * @SWG\Get(
     *   path="/users/create",
     *   summary="Get info necessary to store users",
     *   tags={"Users"},
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
     *
     */
    public function create(Request $request)
    {
        $data = [
            'languages'          => TranslateArrayService::execute(ModelConst::LIST_LANGUAGES_USER_CODE),
            'currencies'         => TranslateArrayService::execute(ModelConst::LIST_CURRENCIES_USER_CODE),
            'document_type_colombian' => TranslateArrayService::execute
            (ModelConst::LIST_TYPE_CARD_DOCUMENT_COLOMBIAN),
        ];

        return $this->successResponseWithMessage($data);

    }


    /**
     * @SWG\Post(
     *   path="/users",
     *   summary="Register user",
     *   tags={"Users"},
     *   consumes={"multipart/form-data"},
     *     @SWG\Parameter(
     *     name="title",
     *     in="formData",
     *     description="User title (1 => Sr, 2 => Mrs, 3 => Ms)",
     *     type="integer",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="name",
     *     in="formData",
     *     description="User name",
     *     type="string",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="last_name",
     *     in="formData",
     *     description="User last name",
     *     type="string",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="password",
     *     in="formData",
     *     description="User password",
     *     type="string",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="password_confirmation",
     *     in="formData",
     *     description="User password confirmation",
     *     type="string",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="email",
     *     in="formData",
     *     description="User Email",
     *     type="string",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="country",
     *     in="formData",
     *     description="Country Id",
     *     type="integer",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="birthdate",
     *     in="formData",
     *     description="Birthdate",
     *     type="string",
     *     format="date-time",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="state",
     *     in="formData",
     *     description="State Id or Name",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="address1",
     *     in="formData",
     *     description="User address1",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="address2",
     *     in="formData",
     *     description="User address2",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="city",
     *     in="formData",
     *     description="City",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="zipcode",
     *     in="formData",
     *     description="Zipcode",
     *     type="integer",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="phone",
     *     in="formData",
     *     description="User phone",
     *     type="string",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="mobile",
     *     in="formData",
     *     description="User mobile",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="ssn",
     *     in="formData",
     *     description="User ssn",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="ssn_type",
     *     in="formData",
     *     description="User ssn type",
     *     type="integer",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="language",
     *     in="formData",
     *     description="User preferred language",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="altEmail",
     *     in="formData",
     *     description="User alternative email",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="utm_source",
     *     in="formData",
     *     description="Utm source",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="utm_campaign",
     *     in="formData",
     *     description="Utm campaign",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="utm_medium",
     *     in="formData",
     *     description="Utm medium",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="utm_content",
     *     in="formData",
     *     description="Utm content",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="utm_term",
     *     in="formData",
     *     description="Utm term",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="cookies",
     *     in="formData",
     *     description="Cookies",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="track",
     *     in="formData",
     *     description="Track",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="cookies_data1",
     *     in="formData",
     *     description="Cookies data1",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="cookies_data2",
     *     in="formData",
     *     description="Cookies data2",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="cookies_data3",
     *     in="formData",
     *     description="Cookies data3",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="cakecookie",
     *     in="formData",
     *     description="Cake cookie",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="pcbr",
     *     in="formData",
     *     description="free spin register",
     *     type="string",
     *     required=false,
     *   ),
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Response(
     *     response=201,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(property="data", allOf={ @SWG\Schema(ref="#/definitions/User"), }),
     *       @SWG\Property(property="code", description="Status Code", example="201"),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=405, ref="#/responses/405"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function store(Request $request) {
        /* This will return the user or trigger a validation response */
        $user = $this->storeUserService->execute($request);

        $sendLogConsoleService = new SendLogConsoleService();
        try {
            $codeResponseFastTrack = FastTrackLogService::registerUser($user->usr_id);
            if ($codeResponseFastTrack != 200) {
                $sendLogConsoleService->execute(request(), 'access', 'access', 'FT API Error - Wrong Code', '');
            }
        } catch (\Exception $exception) {
            $sendLogConsoleService->execute(request(), 'access', 'access', 'FT API Error - Registration: '.$exception->getMessage(), '');
        }
        $promoCode = $this->applyBonusFreeSpinService->execute($request, $user);
        $user->promo_code = $promoCode;
        return $this->showOne($user, 201);
    }

    /**
     * @SWG\Get(
     *   path="/users/details",
     *   summary="Show user details ",
     *   tags={"Users"},
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(property="data", allOf={ @SWG\Schema(ref="#/definitions/User"), }),
     *       @SWG\Property(property="code", description="Status Code", example="200"),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=405, ref="#/responses/405"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function details() {
        return $this->showOne(request()->user());
    }

    /**
     * @SWG\Put(
     *   path="/users",
     *   summary="Update user",
     *   tags={"Users"},
     *   consumes={"application/x-www-form-urlencoded"},
     *     @SWG\Parameter(
     *     name="title",
     *     in="formData",
     *     description="User title (1 => Sr, 2 => Mrs, 3 => Ms)",
     *     type="integer",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="name",
     *     in="formData",
     *     description="User name",
     *     type="string",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="last_name",
     *     in="formData",
     *     description="User last name",
     *     type="string",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="password",
     *     in="formData",
     *     description="User password",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="password_confirmation",
     *     in="formData",
     *     description="User password confirmation",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="country",
     *     in="formData",
     *     description="Country Id",
     *     type="integer",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="birthdate",
     *     in="formData",
     *     description="Birthdate",
     *     type="string",
     *     format="date-time",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="state",
     *     in="formData",
     *     description="State Id or Name",
     *     type="string",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="address1",
     *     in="formData",
     *     description="User address1",
     *     type="string",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="address2",
     *     in="formData",
     *     description="User address2",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="city",
     *     in="formData",
     *     description="City",
     *     type="string",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="zipcode",
     *     in="formData",
     *     description="Zipcode",
     *     type="string",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="phone",
     *     in="formData",
     *     description="User phone",
     *     type="string",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="mobile",
     *     in="formData",
     *     description="User mobile",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="ssn",
     *     in="formData",
     *     description="User ssn",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="ssn_type",
     *     in="formData",
     *     description="User ssn type",
     *     type="integer",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="language",
     *     in="formData",
     *     description="User preferred language",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="altEmail",
     *     in="formData",
     *     description="User alternative email",
     *     type="string",
     *     required=false,
     *   ),
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(property="data", allOf={ @SWG\Schema(ref="#/definitions/User"), }),
     *       @SWG\Property(property="code", description="Status Code", example="200"),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=405, ref="#/responses/405"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */

    public function update_me(Request $request) {
        $user_id = request()->user()->usr_id;
        $user = User::find($user_id);
        if ($user->sys_id != $request->client_sys_id) {
            return $this->errorResponse(trans('lang.invalid_client'), 422);
        }
        $rules = [
            'usr_title' => 'required|integer|exists:mysql_external.users_title,id',
            'usr_name' => 'required|string|max:255',
            'country_id' => 'required|integer|exists:mysql_external.countries',
            'usr_birthdate' => 'required|date_format:"Y-m-d"|before:-18 years',
            'usr_lastname' => 'required|string|max:255',
            'usr_password' => [
                'string',
                'min:6',
                'confirmed',
                new CheckSpamPasswordPeriodUserRule(),
            ],
            'usr_phone' => 'required|string|max:150',
            'usr_mobile' => 'string|max:150',
            'usr_address1' => 'string|max:255',
            'usr_address2' => 'string|max:255',
            'usr_city' => 'string|max:255',
            'usr_state' => 'string|max:255',
            'usr_zipcode' => 'string|max:150',
            'usr_ssn_type' => 'integer|max:7',
            'usr_ssn' => 'string|max:50',
            'usr_language' => 'string|max:45',
            'usr_altEmail' => 'string|email|max:200',
            'currency' => 'nullable|string|max:200',
            'current_password' => [
                'nullable',
                'string',
                'min:6',
                'max:200',
            ],
        ];
        $this->validate($request, $rules);

        [$usr_state] = $this->validationCountyWithStateService->execute($request);

        $old_country = $user->country_id;

        $this->encode($request);
        $step2 = $user->usr_regdate == $user->usr_lastupdate ? true : false;
        $user->usr_title = $request->usr_title ? $request->usr_title : $user->usr_title;
        $user->usr_name = $request->usr_name ? $request->usr_name : $user->usr_name;
        $user->usr_password = $request->usr_password ? $request->usr_password : $user->usr_password;
        $user->country_id = $request->country_id ? $request->country_id : $user->country_id;
        $user->usr_birthdate = $request->usr_birthdate ? $request->usr_birthdate : null;
        $user->usr_lastname = $request->usr_lastname ? $request->usr_lastname : $user->usr_lastname;
        $user->usr_phone = $request->usr_phone ? $request->usr_phone : $user->usr_phone;
        $user->usr_mobile = $request->usr_mobile ? $request->usr_mobile : $user->usr_mobile;
        $user->usr_address1 = $request->usr_address1 ? $request->usr_address1 : $user->usr_address1;
        $user->usr_address2 = $request->usr_address2 ? $request->usr_address2 : $user->usr_address2;
        $user->usr_city = $request->usr_city ? $request->usr_city : $user->usr_city;
        $user->curr_code = $user->curr_code === '' ? $request->currency : $user->curr_code;


        if ($usr_state != "") {
            $user->usr_state = $usr_state;
        } else {
            $user->usr_state = $request->usr_state ? $request->usr_state : $user->usr_state;
        }

        $user->usr_zipcode = $request->usr_zipcode ? $request->usr_zipcode : $user->usr_zipcode;
        $user->usr_ssn = $request->usr_ssn ? $request->usr_ssn : $user->usr_ssn;
        $user->usr_ssn_type = $request->usr_ssn_type ? $request->usr_ssn_type : $user->usr_ssn_type;
        $user->usr_language = $request->usr_language ? $request->usr_language : $user->usr_language;
        $user->usr_altEmail = $request->usr_altEmail ? $request->usr_altEmail : '';

        if ($user->isClean()) {
            return $this->errorResponse(trans('lang.update_clean'), 422);
        }

        /**
         * Change Country: Por el proveedor de juegos RT,
         * necesitamos saber si se cambio el país del usuario para avisar
         * (Se tiene que hacer manual de parte de ellos)
         */
        if($user->isDirty("country_id") && $user->hasPlayedRedTiger()){

            UserDataChangesLog::create([
                "value_to" => $request->country_id,
                "value_from" => $old_country,
                "usr_id" => $user->usr_id,
                "user_field" => "country_id"
            ]);
        }
        /**
         * End Change Country
         */

        $user->save();
        Cache::forget('user_state_' . $user->usr_id);
        Cache::forget('user_country_' . $user->usr_id);
        Cache::forget('user_title_' . $user->usr_id);

        $pixels = collect([]);
        $cookies = $user->usr_cookies != '' ? $user->usr_cookies : null;
        if ($cookies && $step2) {
            $affiliate = explode('_', $cookies)[0];
            $pixel = Pixel::where('sys_id', $request->client_sys_id)
                ->where('pixel_status', 1)
                ->where('pixel_type', 2)
                ->where('pixel_aff_account', $affiliate)->get();
            $pixel->each(function ($item) use ($user, $pixels) {
                $pixels->push(['tag' => $item->PixelTag($user)]);
            });
        }
        $user->pixels = $pixels;
        return $this->showOne($user);
    }

    /**
     * @return mixed
     */
    /**
     * @SWG\Get(
     *   path="/users/last_cart",
     *   summary="Show user last cart",
     *   tags={"Users"},
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(property="data", allOf={ @SWG\Schema(ref="#/definitions/Cart"), }),
     *       @SWG\Property(property="code", description="Status Code", example="200"),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=405, ref="#/responses/405"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function last_cart() {
        $user = request()->user();
        $telemCar = $user->last_cart_telem_attributes;
        $cart_attributes = $telemCar !== null ? $telemCar :
            null;
        if($cart_attributes === null){
            $cart_attributes = $user->last_cart_attributes !== null ? $user->last_cart_attributes : null;
        }
        return $cart_attributes ? $this->showOne($cart_attributes) : $this->errorResponse(trans('lang.no_data'), 422);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @SWG\Get(
     *   path="/user_transactions",
     *   summary="Show user transactions",
     *   tags={"User Transactions"},
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Parameter(
     *     name="identifier",
     *     in="query",
     *     description="Transaction identifier (0 => order, 2 => payment, 5 => deposit)",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="type",
     *     in="query",
     *     description="Transaction type (order, payment, deposit)",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property( property="data", type="array", @SWG\Items(ref="#/definitions/Transaction")),
     *       @SWG\Property(property="code", description="Status Code", example="200"),
     *     ),
     *     @SWG\Schema(
     *       @SWG\Property(
     *         property="data",
     *         type="array",
     *         @SWG\Items(ref="#/definitions/Transaction"),
     *       ),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=405, ref="#/responses/405"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function transactions_list() {
        $user = request()->user();
        return $this->showAllNoPaginated($user->transactions_list);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */

    public function cashier() {
        if(ClientService::isOrca()){
            $client_lang = "en";
        }else{
            $client_lang = request('client_lang') ?
                substr(request('client_lang'), 0, 2) :
                'en';
        }

        return $this->successResponse(['data' => [
                'id' => request()->user()->usr_id,
                'lang' => $client_lang,
                'site' => request('client_site_id')]]
            , 200);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */

    public function language() {
        return $this->successResponse(['data' => ['lang' => $this->getLanguage()]], 200);
    }

    /**
     * @SWG\Post(
     *   path="/users/optin/{user}",
     *   summary="Update user optin",
     *   tags={"Users"},
     *   consumes={"multipart/form-data"},
     *   @SWG\Parameter(
     *     name="user",
     *     in="path",
     *     description="User Id",
     *     type="integer",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="is_blocked",
     *     in="formData",
     *     description="Is Blocked (0,1)",
     *     type="integer",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="is_excluded",
     *     in="formData",
     *     description="Is Excluded (0,1)",
     *     type="integer",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="is_sms",
     *     in="formData",
     *     description="Is SMS (0,1)",
     *     type="integer",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="is_email",
     *     in="formData",
     *     description="Is Mail (0,1)",
     *     type="integer",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="is_push",
     *     in="formData",
     *     description="Is Push Notification (0,1)",
     *     type="integer",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="is_direct_mail",
     *     in="formData",
     *     description="Is Direct Mail (0,1)",
     *     type="integer",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="is_phone_direct",
     *     in="formData",
     *     description="Is Phone Direct (0,1)",
     *     type="integer",
     *     required=false,
     *   ),
     *   security={
     *     {"client_credentials": {}},
     *   },
     *   @SWG\Response(
     *     response=201,
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

    public function optin(Request $request, User $user) {
        $client_sys_id = Client::where('id', request()->oauth_client_id)->first()->site->system->sys_id;
        $user_sys_id = $user->site->sys_id;
        if ($user_sys_id != $client_sys_id) {
            return $this->errorResponse(trans('lang.invalid_client'), 422);
        }
        $rules = [
            'is_blocked' => 'integer|min:0|max:1',
            'is_email' => 'integer|min:0|max:1',
            'is_phone_direct' => 'integer|min:0|max:1',
            'is_excluded' => 'integer|min:0|max:1',
            'is_sms' => 'integer|min:0|max:1',
            'is_push' => 'integer|min:0|max:1',
            'is_direct_mail' => 'integer|min:0|max:1',
        ];
        $this->validate($request, $rules);
        if (isset($request->is_blocked)) {
            if ($request->is_blocked == 1) {
                $user->usr_active = 0;
                $user->usr_level = 0;
            } else {
                $user->usr_active = 1;
            }
        }
        if (isset($request->is_email)) {
            if ($request->is_email == 1) {
                $user->usr_nopromoemails = 0;
            } else {
                $user->usr_nopromoemails = 1;
            }
        }
        if (isset($request->is_phone_direct)) {
            if ($request->is_phone_direct == 1) {
                $user->usr_notTelemCall = 0;
            } else {
                $user->usr_notTelemCall = 1;
            }
        }
        if (isset($request->is_excluded)) {
            $this->_optin('optin_exclusions', $user->usr_id, $request->is_excluded);
        }
        if (isset($request->is_sms)) {
            $this->_optin('optin_sms', $user->usr_id, $request->is_sms);
        }
        if (isset($request->is_push)) {
            $this->_optin('optin_push', $user->usr_id, $request->is_push);
        }
        if (isset($request->is_direct_mail)) {
            $this->_optin('optin_direct_mail', $user->usr_id, $request->is_direct_mail);
        }
        if ($user->isDirty()) {
            $user->save();
        }
        return $this->successResponse(['data' => 'Success'], 200);
    }

    private function _optin($table, $usr_id, $value) {

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
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @SWG\Get(
     *   path="/users/wallet",
     *   summary="Get user wallet",
     *   tags={"Users"},
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(property="data", allOf={ @SWG\Schema(ref="#/definitions/UserWallet"), }),
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
    public function user_extra_details() {
        $user = request()->user();
        $user->transformer = UserExtraDetailsTransformer::class;
        return $this->showOne($user);
    }


    /**
     * @SWG\Get(
     *   path="/users/winnings",
     *   summary="Show user winnings",
     *   tags={"Users"},
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(property="data", allOf={ @SWG\Schema(ref="#/definitions/UserWinnings"), }),
     *       @SWG\Property(property="code", description="Status Code", example="200"),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=405, ref="#/responses/405"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     */
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function winnings() {
        $list = request()->user()->winnings();
        return $this->successResponse(['data' => $list->isEmpty() ? [] : $list], 200);
    }

    /**
     * @SWG\Get(
     *   path="/users/winnings_pending",
     *   summary="Show user pending winnings",
     *   tags={"Users"},
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(property="data", allOf={ @SWG\Schema(ref="#/definitions/UserWinnings"), }),
     *       @SWG\Property(property="code", description="Status Code", example="200"),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=405, ref="#/responses/405"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     */
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function winnings_pending() {
        $list = request()->user()->winnings_pending();
        return $this->successResponse(['data' => $list->isEmpty() ? [] : $list], 200);
    }


    /**
     * @SWG\Post(
     *   path="/users/winnings_details",
     *   summary="Show user winnings details",
     *   tags={"Users"},
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Parameter(
     *     name="identifier",
     *     in="formData",
     *     description="Subscription Identifier",
     *     type="number",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="product_type_identifier",
     *     in="formData",
     *     description="Product Type Identifier",
     *     type="number",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="product_identifier",
     *     in="formData",
     *     description="Product Identifier",
     *     type="number",
     *     required=true,
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property( property="data", allOf={ @SWG\Schema(ref="#/definitions/UserWinningsLottery"), }),
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
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function winnings_details(Request $request) {

        $table_keys = [
            1 => [ // Lotteries
                'identifier' => [
                    'table' => 'tickets',
                    'column' => 'tck_id'
                ],
                'product_identifier' => 'lot_id'
            ],
            10 => [ // Live Lotteries
                'identifier' => [
                    'table' => 'subscriptions',
                    'column' => 'sub_id'
                ],
                'product_identifier' => 'lot_id'
            ],
            4 => [ // Raffles
                'identifier' => [
                    'table' => 'raffles_tickets',
                    'column' => 'rsub_id'
                ],
                'product_identifier' => 'rff_id'
            ],
            2 => [ // Syndicates
                'identifier' => [
                    'table' => 'syndicate_prizes',
                    'column' => 'id'
                ],
                'product_identifier' => 'id'
            ],
            3 => [ // Syndicates Raffles
                'identifier' => [
                    'table' => 'syndicate_raffle_prizes',
                    'column' => 'id'
                ],
                'product_identifier' => 'id'
            ],/*
            7=>[ // Scratches
                'identifier'=>[
                    'table'=>'scratches_subscriptions',
                    'column'=>'scratches_sub_id'
                ],
                'product_identifier'=>'id'
            ],*/
        ];

        $this->validate($request, ['product_type_identifier' => 'required|integer|' . Rule::in(array_keys($table_keys))]);
        $rules = [
            'identifier' => 'required|integer|' . Rule::exists('mysql_external.' . $table_keys[$request->product_type_identifier]['identifier']['table'], $table_keys[$request->product_type_identifier]['identifier']['column']),
            'product_type_identifier' => 'required|integer|' . Rule::exists('product_types', 'id'),
            'product_identifier' => 'required|integer|' . Rule::exists('mysql_external.' . ProductType::find($request->product_type_identifier)->product_table_name, $table_keys[$request->product_type_identifier]['product_identifier']),
        ];
        $this->validate($request, $rules);
        switch ($request->product_type_identifier) {
            case 1: // Lotteries
                $data = Ticket::where('tck_id', '=', $request->identifier)
                    ->whereHas('subscription', function ($query) use ($request) {
                        $query->where('usr_id', '=', $request->user_id);
                    })
                    ->first();
                if ($data) {
                    $data->transformer = UserWinningsLotteryTransformer::class;
                } else {
                    return $this->errorResponse(trans('lang.lottery_forbidden'), 422);
                }
                break;
            case 10: // Live Lotteries
                $data = LiveLotterySubscription::where('sub_id', '=', $request->identifier)
                    ->where('usr_id', '=', $request->user_id)
                    ->first();
                if ($data) {
                    $data->transformer = UserWinningsLiveLotteryTransformer::class;
                }//$data->transformer = UserWinningsLiveLotteryTransformer::class;
                else {
                    return $this->errorResponse(trans('lang.lottery_live_forbidden'), 422);
                }
                break;

            case 4: // Raffles
                $data = RaffleSubscription::where('rsub_id', '=', $request->identifier)
                    ->where('usr_id', '=', $request->user_id)
                    ->whereHas('tickets_winnings', function ($query) use ($request) {
                        $query->where('rff_id', '=', $request->product_identifier);
                    })
                    ->first();
                if ($data) {
                    $data->transformer = UserWinningsRaffleTransformer::class;
                } else {
                    return $this->errorResponse(trans('lang.raffle_forbidden'), 422);
                }
                break;
            case 2: // Syndicates
                $data = SyndicatePrize::where('id', '=', $request->identifier)
                    ->where('usr_id', '=', $request->user_id)
                    ->first();
                if ($data) {
                    $data->transformer = UserWinningsSyndicateTransformer::class;
                } else {
                    return $this->errorResponse(trans('lang.syndicate_subscription_forbidden'), 422);
                }
                break;
            case 3: // Syndicates Raffles
                $data = SyndicateRafflePrize::where('id', '=', $request->identifier)->where('usr_id', '=', $request->user_id)
                    ->first();
                if ($data) {
                    $data->transformer = UserWinningsSyndicateRaffleTransformer::class;
                } else {
                    return $this->errorResponse(trans('lang.raffle_syndicate_forbidden'), 422);
                }
                break;
            case 7: // Scratches
            default:
                return $this->errorResponse(trans('lang.no_data'), 422);
                break;
        }
        return $this->showOne($data);
    }

    /**
     * @SWG\Post(
     *   path="/users/validate_email",
     *   summary="Validate user email",
     *   tags={"Users"},
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Parameter(
     *     name="email",
     *     in="formData",
     *     description="User Email",
     *     type="string",
     *     required=true,
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(property="data", description="Validation Message", example="Valid email"),
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
    public function validate_email(Request $request) {
        $rules = [
            'usr_email' => 'required|string|email|max:255|' . Rule::unique('mysql_external.users')->where('sys_id', $request->client_sys_id),
        ];
        $this->validate($request, $rules);
        return $this->showMessage(trans('lang.valid_email'));
    }

    /**
     * @SWG\Post(
     *   path="/ip_who_is",
     *   summary="Ip who is",
     *   tags={"Authentication"},
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Parameter(
     *     name="curr_code",
     *     in="formData",
     *     description="curr code to get symbol currency",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="language",
     *     in="formData",
     *     description="User language",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Redirect not needed",
     *     @SWG\Schema(
     *     @SWG\Property(
     *       property="data",
     *       type="object",
     *         @SWG\Property(property="redirect", description="Redirect", type="boolean", example=false),
     *         @SWG\Property(property="client_id", description="Client id", type="integer", example="100600"),
     *         @SWG\Property(property="domain", description="Domain", type="string", example="https://wintrillions.com"),
     *         @SWG\Property(property="client_currency", description="Client Currency", type="string", example="USD"),
     *         @SWG\Property(property="client_lang", description="Client Lang", type="string", example="en"),
     *         @SWG\Property(property="region", description="Region Code", type="string", example="LATAM"),
     *     ),
     *     ),
     *   ),
     *   @SWG\Response(
     *     response=302,
     *     description="Redirect needed",
     *     @SWG\Schema(
     *     @SWG\Property(
     *       property="data",
     *       type="object",
     *         @SWG\Property(property="redirect", description="Redirect", type="boolean", example=true),
     *         @SWG\Property(property="client_id", description="Client id", type="integer", example="100601"),
     *         @SWG\Property(property="domain", description="Domain", type="string", example="https://fr.trillonario.com"),
     *         @SWG\Property(property="client_currency", description="Client Currency", type="string", example="USD"),
     *         @SWG\Property(property="client_lang", description="Client Lang", type="string", example="fr"),
     *         @SWG\Property(property="region", description="Region Code", type="string", example="LATAM"),
     *       )
     *    ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(
     *     response=403,
     *     description="Blocked country",
     *     @SWG\Schema(
     *       @SWG\Property(
     *            property="error",
     *            type="object",
     *            @SWG\Property(property="blocked_country", description="Blocked Country", type="array", @SWG\Items(example="Blocked Country")),
     *       ),
     *       @SWG\Property(property="code", description="Status Code", example="403"),
     *     ),
     *   ),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=405, ref="#/responses/405"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */

    public function ip_who_is(Request $request) {

        $origin = GetOriginRequestService::execute();
        $activeExceptionDomain = env('DOMAIN_STATIC_EXCEPTION',null) === $origin;
        if($activeExceptionDomain === true){
            $response = [
                'client_id' => $request['oauth_client_id'],
                'domain' => $origin,
                'client_currency' => null,
                'client_lang' => null,
                'region' => null,
                'redirect_code' => 200, // no redirect
                'reason' => 'static_domain_no_redirect',
            ];
            $data = collect($response);
            $this->setDataIpLocation($data, $request);
            return $this->successResponse(['data' => $data], 200);
        }

        // Blocked countries
        $blocked_country = ClientProductCountryBlacklist::query()
            ->where('clients_products_id', '=', 0)
            ->where('product_type_id', '=', 0)
            ->where('country_id', '=', $request[ 'client_country_id' ])
            ->getFromCache();

        if ($blocked_country->isNotEmpty()) {
            return $this->errorResponse(trans('lang.not_valid_ip_country'), 403);
        }

        $latam = $this->rememberCache('latam_ISOS' , Config::get('constants.cache_daily'), function () {
            $countries = Country::where('country_region', '=', 1)->select('country_Iso')->get();
            $isos = [];
            $countries->each(function ($item) use (&$isos){
                $isos []= $item->country_Iso;
            });
            return $isos;
        });

        $country_region_id = $request['client_country_region'];
        $country_region = CountryRegion::where('country_region_id', '=', $country_region_id)->first();
        $country_region_code = ($country_region != "" && $country_region != null) ? $country_region->country_region_code : "ROW";

        // Special cases, Our IPs
        if ($request->user_ip == '54.207.106.119' || $request->user_ip == '200.125.24.206') {
            $data = collect([
                'client_id' => $request['oauth_client_id'],
                'domain' => $request->user_ip,
                'client_currency' => 'USD',
                'client_lang' => 'en',
                'region' => $country_region_code,
                'redirect_code' => 200, // no redirect
                'reason' => 'internal_ip',
            ]);

            $this->setDataIpLocation($data, $request);
            return $this->successResponse(['data' => $data], 200);

        }


        // Regions redirection
        $client_special_domains = Client::where('partner_id', '=', $request['client_partner'])
            ->where('country_iso', '=', $request['client_country_iso'])
            ->first();

        [ $activeDirtyUkGb, $data ] = $this->dirtyExceptionRedirectUkGbService->execute(
            $request
        );

        if($activeDirtyUkGb === true){
            $this->setDataIpLocation($data, $request);
            return $this->successResponse(['data' => $data], 200);
        }

        if ($client_special_domains) {
            $iso = $client_special_domains->country_iso;
            $country = $this->rememberCache('country_' . $iso, Config::get('constants.cache_daily'), function () use ($iso) {
                return Country::with('country_info')->where('country_Iso', $iso)->get();
            });
            $client_currency = $country->first() ? $country->first()->country_info ? $country->first()->country_info->country_currency : null : null;
            $client_lang = $client_special_domains->site ? $client_special_domains->site->site_lang_code : null;
            $client_domain = $client_special_domains->site ? $client_special_domains->site->site_url_https : null;
            if ($client_special_domains->id == $request['oauth_client_id']) {
                $data = collect([
                    'client_id' => $client_special_domains->id,
                    'domain' => $client_special_domains->name,
                    'client_currency' => $client_currency,
                    'client_lang' => substr($client_lang, 0, 2),
                    'region' => $country_region_code,
                    'redirect_code' => 200, // no redirect
                    'reason' => 'special_domains_no_redirection',
                ]);
            } else {
                $data = collect([
                    'client_id' => $client_special_domains->id,
                    'domain' => $client_domain,
                    'client_currency' => $client_currency,
                    'client_lang' => substr($client_lang, 0, 2),
                    'region' => $country_region_code,
                    'redirect_code' => 302, // redirect
                    'reason' => 'special_domains_redirection',
                ]);
            }


            $this->setDataIpLocation($data, $request);
            return $this->successResponse(['data' => $data], 200);

        } elseif (in_array($request['client_country_iso'], $latam)) { // LATAM client
            $latam_client = Client::where('partner_id', '=', $request['client_partner'])
                ->where('country_iso', '=', 'LM')
                ->first();
            $client_domain = $latam_client !== null && $latam_client->site ?
                $latam_client->site->site_url_https :
                null;
            if ($latam_client !== null && $latam_client->id != $request['oauth_client_id']) {
                $data = collect([
                    'client_id' => $latam_client->id,
                    'domain' => $client_domain,
                    'client_currency' => 'USD',
                    'client_lang' => 'es',
                    'region' => $country_region_code,
                    'redirect_code' => 302, // redirect
                    'reason' => 'latam_redirection',
                ]);
            } else {
                $data = collect([
                    'client_id' => $request['oauth_client_id'],
                    'domain' => $client_domain,
                    'client_currency' => 'USD',
                    'client_lang' => 'es',
                    'region' => $country_region_code,
                    'redirect_code' => 200, // no redirect
                    'reason' => 'latam_no_redirection',
                ]);
            }
        } else { // Default client
            $client_default = Client::where('partner_id', '=', $request['client_partner'])
                ->whereNull('country_iso')
                ->first();
            $client_domain = $client_default->site ? $client_default->site->site_url_https : null;
            if ($client_default->id != $request['oauth_client_id']) {
                $data = collect([
                    'client_id' => $client_default->id,
                    'domain' => $client_domain,
                    'client_currency' => 'USD',
                    'client_lang' => 'en',
                    'region' => $country_region_code,
                    'redirect_code' => 302, // redirect
                    'reason' => 'default_client_redirection',
                ]);
            } else {

                if ($client_domain != $request['client_domain']) {
                    $data = collect([
                        'client_id' => $client_default->id,
                        'domain' => $request['client_domain'],
                        'client_currency' => 'USD',
                        'client_lang' => 'en',
                        'region' => $country_region_code,
                        'redirect_code' => 302, // redirect
                        'reason' => 'default_client_dif_domain',
                    ]);
                } else {
                    $data = collect([
                        'client_id' => $request['oauth_client_id'],
                        'domain' => $client_domain,
                        'client_currency' => 'USD',
                        'client_lang' => 'en',
                        'region' => $country_region_code,
                        'redirect_code' => 200, // no redirect
                        'reason' => 'default_client_same_domain',
                    ]);
                }


            }
        }

        $this->setDataIpLocation($data, $request);
        return $this->successResponse(['data' => $data], 200);
    }

    private function setDataIpLocation($data, $request)
    {
        [ , , $dataIp2Location ] = IP2LocTrillonario::get_iso('');
        if($dataIp2Location !== null){
            $dataIp2Location['ip'] = $request->user_ip;
        }

        $currCode = $request->country_currency;
        if ($request->curr_code !== null) {
            $currCode = $request->curr_code;
        }
        $currency = Currency::query()
            ->where('curr_code', $currCode)
            ->firstFromCache([ 'curr_code', 'curr_symbol' ]);
        $data->put('curr_code_request', $request->curr_code);
        $data->put('currency_info', $currency);
        $data->put('ip2location', $dataIp2Location);
        $data->put('client_from_wrapper', $request['oauth_client_id']);
        $data->put('country_currency_from_request', $request['country_currency']);

        return $data;
    }

    /**
     * @SWG\Get(
     *   path="/users/logout",
     *   summary="Logout user",
     *   tags={"Authentication"},
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(property="data", description="Validation Message", example="Successfull user logout"),
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
    public function logout() {
        request()->user()->token()->delete();
        return $this->showMessage(trans('lang.logout'));
    }


    /**
     * @SWG\Post(
     *   path="/users/quick_deposit",
     *   summary="Quick deposit",
     *   tags={"Users"},
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Parameter(
     *     name="amount",
     *     in="formData",
     *     description="Amount",
     *     type="integer",
     *     required=true,
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(property="data", type="object",
     *         @SWG\Property(
     *         property="status",
     *         description="Deposit Status",
     *         type="integer",
     *         example="0"
     *        ),
     *        @SWG\Property(
     *         property="crt_id_created",
     *         description="Cart id",
     *         type="integer",
     *         example="0"
     *        ),
     *        @SWG\Property(
     *         property="message",
     *         description="Response message result",
     *         type="string",
     *         example="Last cart deposit check failed"
     *        ),
     *       ),
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

    public function quick_deposit(Request $request) {
        $rules = [
            'amount' => 'required|integer',
        ];
        $this->validate($request, $rules);
        $quick_deposit = $request->user()->quick_deposit;
        //$quick_deposit = 20142256;
        if ($quick_deposit == 0) {
            return $this->errorResponse(trans('lang.deposit_forbidden'), 422);
        }
        $cashierToken = "Y)NM~X!fCwLWY!.Nt2#zwym8.5(hw_X>z\>2]@DLm=hQ_!jcas";
        $url = Site::find($request['client_site_id'])->cashier_url_https . '/quickdeposit.php';
        $user_id = $request['user_id'];
        //$user_id = 40433;
        $httpClient = new ClientHttp();
        try {
            $response = $httpClient->post($url, [
                'form_params' => [
                    'usr_id' => $user_id,
                    'last_cart_deposited' => $quick_deposit,
                    'amount_to_deposit' => $request->amount,
                    'currency_to_deposit' => $request['country_currency']
                ],
                'headers' => [
                    'CASHIERTOKEN' => $cashierToken,
                ],
            ]);
            $response = json_decode($response->getBody()->getContents(), true);
            return $this->successResponse(['data' => $response], 200);
        } catch (\Exception $exception) {
            $infoEndpoint = GetInfoFromExceptionService::execute($request, $exception);
            $this->sendErrorNotification(
                $infoEndpoint,
                ModelConst::QUICK_DEPOSIT_ERROR
            );
            return $this->showMessage('Quick deposit exception', 200);
        }
    }

    /**
     * @SWG\Get(
     *   path="/users/membership_benefits",
     *   summary="Show user membership benefits",
     *   tags={"Users"},
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(property="data", type="array", @SWG\Items()),
     *       @SWG\Property(property="code", description="Status Code", example="200"),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=405, ref="#/responses/405"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     */
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function membership_benefits() {
        $membership_benefits = request()->user()->membership_benefits();
        return $this->successResponse(['data' => $membership_benefits->isEmpty() ? [] : $membership_benefits], 200);
    }

    /**
     * @SWG\Get(
     *   path="/index_pixel",
     *   summary="Show index pixel",
     *   tags={"Authentication"},
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(property="data", description="Pixel", example="<script type='text/javascript'>rtgsettings ={'pdt_url': 'http://wintrillions.com?account=29b6e3c2&cpa=yes&utm_medium=retargeting&utm_source=netgroupmedia&utm_campaign=tri','pagetype': 'home','key': 'GTM','token': 'Trillonario_GBL','layer': 'iframe'};(function(d) {var s = d.createElement('script'); s.async = true;s.id='madv2014rtg';s.type='text/javascript';s.src = (d.location.protocol == 'https:' ? 'https:' : 'http:') + '//www.mainadv.com/Visibility/Rtggtm2-min.js';var a = d.getElementsByTagName('script')[0]; a.parentNode.insertBefore(s, a);}(document));</script>"),
     *       @SWG\Property(property="code", description="Status Code", example="200"),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=405, ref="#/responses/405"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     */
    public function index_pixels() {
        $pixels = $this->pixels_index() ? $this->pixels_index() : null;
        return $this->successResponse(['data' => ['pixels' => [$pixels ? $pixels : '']]], 200);
    }

    /**
     * @SWG\Get(
     *   path="/util-laravel/clear_cache",
     *   summary="clear cache query and other process",
     *   tags={"Utils Laravel"},
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
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
    public function clear_cache() {
        $clearCache = Artisan::call('cache:clear');
        $tagDefault = config('constants.cache_tag_name');
        $this->forgetCacheByTag($tagDefault);
        return $this->successResponseWithMessage(['clear_cache'=> $clearCache], 'cache clean');
    }

    public function testIp2Lock()
    {
        return IP2LocTrillonario::get_iso('');
    }
}
