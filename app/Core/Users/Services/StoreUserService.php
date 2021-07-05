<?php

namespace App\Core\Users\Services;


use App\Core\Users\Services\GetPixelByUserService;
use App\Core\Rapi\Models\Pixel;
use App\Services\User\Illuminate;
use App\Services\User\JsonResponse;
use App\Services\User\Symfony;
use App\Core\Users\Services\ValidationCountyWithStateService;
use App\Core\Users\Models\User;
use App\Core\Users\Models\UserCakeInfo;
use App\Core\Base\Traits\ApiResponser;
use App\Core\Base\Traits\Encoding;
use App\Core\Base\Traits\Utils;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StoreUserService
{

    use ApiResponser, Encoding, Utils, ValidatesRequests;

    /**
     * @var ValidationCountyWithStateService
     */
    private $validationCountyWithStateService;

    public function __construct(ValidationCountyWithStateService $validationCountyWithStateService)
    {
        $this->validationCountyWithStateService = $validationCountyWithStateService;
    }

    /**
     * Public method to create a user
     *
     * @param   Request $request
     * @throws  Illuminate\Validation\ValidationException
     *                                                 Exception with validation array
     * @throws  Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException
     *                                                 Exception with validation message
     * @return  JsonResponse|User
     */
    public function execute(Request $request)
    {
        $rules = [
            'usr_title' => 'integer|exists:mysql_external.users_title,id',
            'usr_name' => 'required|string|max:255',
            'usr_email' => 'required|string|email|max:255|' . Rule::unique('mysql_external.users')->where('sys_id', $request->client_sys_id),
            'usr_password' => 'required|string|min:6|confirmed',
            'country_id' => 'required|integer|exists:mysql_external.countries',
            'usr_birthdate' => 'date_format:"Y-m-d"|before:-18 years',
            'usr_lastname' => 'required|string|max:255',
            'usr_phone' => 'required|string|max:150',
            'usr_mobile' => 'string|max:150',
            'usr_address1' => 'string|max:255',
            'usr_address2' => 'string|max:255',
            'usr_city' => 'string|max:255',
            'usr_zipcode' => 'string|max:150',
            'usr_ssn_type' => 'integer|max:7',
            'usr_ssn' => 'string|max:50',
            'usr_language' => 'string|max:45',
            'usr_altEmail' => 'string|email|max:200',
            'utm_source' => 'string|max:255',
            'utm_campaign' => 'string|max:255',
            'utm_medium' => 'string|max:255',
            'utm_content' => 'string|max:255',
            'utm_term' => 'string|max:255',
            'usr_cookies' => 'string|max:255',
            'usr_track' => 'string|max:255',
            'usr_cookies_data4' => 'string|max:255',
            'usr_cookies_data5' => 'string|max:255',
            'usr_cookies_data6' => 'string|max:255',
            'usr_internal_account' => 'integer|max:1|min:0',
        ];
        $this->validate($request, $rules);
        $this->encode($request);

        [$usr_state] = $this->validationCountyWithStateService->execute($request);

        $user = new User();
        $user->usr_title = $request->usr_title ? $request->usr_title : 0;
        $user->usr_name = $request->usr_name;
        $user->usr_email = $request->usr_email;
        $user->usr_password = $request->usr_password;
        $user->usr_birthdate = $request->usr_birthdate ? $request->usr_birthdate : null;
        $user->country_id = $request->country_id;
        $user->usr_lastname = $request->usr_lastname ? $request->usr_lastname : '';
        $user->usr_phone = $request->usr_phone;
        $user->usr_mobile = $request->usr_mobile ? $request->usr_mobile : '';
        $user->usr_address1 = $request->usr_address1 ? $request->usr_address1 : '';
        $user->usr_address2 = $request->usr_address2 ? $request->usr_address2 : '';
        $user->usr_city = $request->usr_city ? $request->usr_city : '';

        if ($usr_state != "") {
            $user->usr_state = $usr_state;
        } else {
            $user->usr_state = $request->usr_state ? $request->usr_state : '';
        }

        $user->usr_zipcode = $request->usr_zipcode ? $request->usr_zipcode : '';
        $user->usr_ssn_type = $request->usr_ssn_type ? $request->usr_ssn_type : 0;
        $user->usr_ssn = $request->usr_ssn ? $request->usr_ssn : '';
        $user->usr_language = $request->usr_language ? $request->usr_language : $this->getLanguageCode();
        $user->usr_altEmail = $request->usr_altEmail ? $request->usr_altEmail : '';
        $user->curr_code = $request['country_currency'] ? $request['country_currency'] : '';
        if($request->currency_empty !== null){
            $user->curr_code = $request->currency_empty !== 'yes' ? $request['country_currency'] : '';
        }
        $user->utm_source = $request->utm_source ? $request->utm_source : '';
        $user->utm_campaign = $request->utm_campaign ? $request->utm_campaign : '';
        $user->utm_medium = $request->utm_medium ? $request->utm_medium : '';
        $user->utm_content = $request->utm_content ? $request->utm_content : '';
        $user->utm_term = $request->utm_term ? $request->utm_term : '';
        $user->usr_cookies = $request->usr_cookies ? $request->usr_cookies : '';
        $user->usr_track = $request->usr_track ? $request->usr_track : '';
        $user->usr_cookies_data4 = $request->usr_cookies_data4 ? $request->usr_cookies_data4 : '';
        $user->usr_cookies_data5 = $request->usr_cookies_data5 ? $request->usr_cookies_data5 : '';
        $user->usr_cookies_data6 = $request->usr_cookies_data6 ? $request->usr_cookies_data6 : '';
        $user->usr_internal_account = $request->usr_internal_account ? $request->usr_internal_account : 0;
        $user->site_id = $request->client_site_id;
        $user->sys_id = $request->client_sys_id;
        $user->usr_active = 1;
        $user->usr_aid = sha1($request->usr_email);
        $user->usr_regdate = now()->format('Y-m-d H:i:s');
        $user->usr_lastLogin = now()->format('Y-m-d H:i:s');
        $user->usr_ip = $request->user_ip;
        $user->save();

        $user = GetPixelByUserService::execute($request, $user);

        $cake_cookies = $request->filled('cakecookie') ? $request->cakecookie : 0;

        if($cake_cookies != ''){
            /*cookie set: $_REQUEST['cketype']."_".$_REQUEST['campaignid']."_".$_REQUEST['trackingid']."_".$_REQUEST['affiliateid']*/
            $cke_cookies = explode("_",$cake_cookies);
            $eventname = $cke_cookies[0];
            $eventnametoreg = "rev";
            if($eventname == 1){
                $eventnametoreg = "cpa";
            }elseif ($eventname == 3){
                $eventnametoreg = "hybrid";
            }elseif ($eventname == 4){
                $eventnametoreg = "cpl";
            }else if($eventname == 5){
                $eventnametoreg = "fixedcost";
            }
            UserCakeInfo::create([
                'usr_id' => $user->usr_id,
                'affiliate_id' => $cke_cookies[3],
                'campaign_id' => $cke_cookies[1],
                'tracking_id' => $cke_cookies[2],
                'eventname' => $eventnametoreg,
                'cpastatus' => 0
            ]);
        }

        return $user;
    }
}
