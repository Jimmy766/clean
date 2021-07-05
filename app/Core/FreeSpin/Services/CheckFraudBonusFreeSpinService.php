<?php

namespace App\Core\FreeSpin\Services;

use App\Core\Base\Classes\ModelConst;
use App\Core\Base\Services\TranslateTextService;
use App\Core\Countries\Models\Country;
use App\Core\Clients\Models\Ip2Location;
use App\Core\FreeSpin\Models\OneTimeAllowedEmails;
use App\Core\Rapi\Models\Proxy;
use App\Core\Base\Services\SendLogConsoleService;
use App\Core\Users\Models\User;
use App\Core\Users\Models\UserLogin;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class CheckFraudBonusFreeSpinService
 * @package App\Services
 */
class CheckFraudBonusFreeSpinService
{

    public function execute($request, $user, $promotion)
    {
        if ( $promotion->freespins_type === ModelConst::LOGIN_FREE_SPIN ) {
            return false;
        }

        $this->isAllowedMail($user, $promotion);
        $this->userExists($user, $promotion);
        $this->usedIp($request->user_ip, $user, $promotion);
        $this->isFreeMail($user, $promotion);
        $this->usesProxy($user,$promotion);
        $this->ipInProxyList($request->user_ip, $user, $promotion);
        $this->countryMatches($request->user_ip, $user, $promotion);
        $this->duplicateAccountOk($user, $promotion);
    }

    private function isAllowedMail($user, $promotion)
    {
        $allowed = OneTimeAllowedEmails::query()
            ->where('email', $user->usr_email)
            ->first();

        if ($allowed !== null) {
            $message                   = 'is_not_allowed_email';
            $array[ 'possible_fraud' ] = $message;
            $array[ 'type_fraud' ]     = 'is_not_allowed_email';
            $array[ 'id_user' ]        = $user->usr_id;
            $array[ 'id_promo_code' ]  = $promotion->id;

            $sendLogConsoleService = new \App\Core\Base\Services\SendLogConsoleService();
            $sendLogConsoleService->execute(request(), 'promo-code', 'access', 'terminate', $array);

            throw new UnprocessableEntityHttpException(
                $message, null, Response::HTTP_BAD_REQUEST
            );
        }

    }

    private function userExists($user, $promotion)
    {
        if (ModelConst::CHECK_NAME_FREE_SPIN === false) {
            return false;
        }
        if ( $promotion->freespins_type === ModelConst::REGISTER_FREE_SPIN ) {
            return false;
        }

        $user = User::query()
            ->where('usr_name', $user->usr_name)
            ->where('usr_lastname', $user->usr_lastname)
            ->first();
        if ($user === null) {
            $message                   = 'user_does_not_exist';
            $array[ 'possible_fraud' ] = $message;
            $array[ 'type_fraud' ]     = 'user_does_not_exist';
            $array[ 'id_user' ]        = $user->usr_id;
            $array[ 'id_promo_code' ]  = $promotion->id;

            $sendLogConsoleService = new SendLogConsoleService();
            $sendLogConsoleService->execute(request(), 'promo-code', 'access', 'terminate', $array);
            throw new UnprocessableEntityHttpException(
                $message, null, Response::HTTP_BAD_REQUEST
            );
        }
        return true;
    }

    private function usedIp($ip, $user, $promotion)
    {
        if (ModelConst::CHECK_IP_FREE_SPIN === false) {
            return false;
        }

        $interval  = ModelConst::INTERVAL_HOUR_FREE_SPIN;
        $userLogin = UserLogin::query()
            ->where('log_ip', $ip)
            ->whereRaw("log_date > date_sub(NOW(), interval {$interval} hour)")
            ->get();
        if ($userLogin->count() > 1) {
            $message                   = 'ip_less_hour_used';
            $array[ 'possible_fraud' ] = $message;
            $array[ 'type_fraud' ]     = 'ip_less_hour_used';
            $array[ 'id_user' ]        = $user->usr_id;
            $array[ 'id_promo_code' ]  = $promotion->id;
            $array[ 'ip_user' ]        = $ip;
            $sendLogConsoleService     = new SendLogConsoleService();
            $sendLogConsoleService->execute(request(), 'promo-code', 'access', 'terminate', $array);
            throw new UnprocessableEntityHttpException(
                $message, null, Response::HTTP_BAD_REQUEST
            );
        }

        return true;
    }

    private function isFreeMail($user, $promotion)
    {
        if (ModelConst::NO_FREE_MAIL_FREE_SPIN === false) {
            return false;
        }
        $email = $user->email;

        /*	'if not isAllowedMail(mail) then

                'Dim start, domain
                'Dim cnn, rs, sql

                'set cnn = obtenerConexion()
                'set rs = obtenerRecordset()

                'start = instr(mail, "@")
                'domain = mid(mail, start)

                'No me funciona con =, aparentemente tiene un espacio luego del dominio pero se ve ni lo puedo quitar
                'sql = "SELECT domain FROM free_mail_domains WHERE domain LIKE '" & domain & "%'"
                'rs.open sql, cnn

                'if NOT rs.EOF then
                    'isFreeMail = true
                'else
                    'isFreeMail = false
                'end if

                'rs.close
            'else
                'isFreeMail = false
            'end if
            */
        return false;
    }

    private function usesProxy($user, $promotion)
    {
        if (ModelConst::FORBID_TRANSPARENT_PROXY_FREE_SPIN === false) {
            return false;
        }

        $proxy = '';
        if ($_SERVER[ "HTTP_X_FORWARDED_FOR" ]) {
            if ($_SERVER[ "HTTP_CLIENT_IP" ]) {
                $proxy = $_SERVER[ "HTTP_CLIENT_IP" ];
            } else {
                $proxy = $_SERVER[ "REMOTE_ADDR" ];
            }
        }

        if ($proxy != '') {
            $message                   = 'use_of_proxy';
            $array[ 'possible_fraud' ] = $message;
            $array[ 'type_fraud' ]     = 'use_of_proxy';
            $array[ 'id_user' ]        = $user->usr_id;
            $array[ 'id_promo_code' ]  = $promotion->id;
            $sendLogConsoleService     = new \App\Core\Base\Services\SendLogConsoleService();
            $sendLogConsoleService->execute(request(), 'promo-code', 'access', 'terminate', $array);
            throw new UnprocessableEntityHttpException(
                $message, null, Response::HTTP_BAD_REQUEST
            );
        }
        return true;
    }

    private function ipInProxyList($ip, $user, $promotion)
    {
        if (ModelConst::CHECK_PROXY_FREE_SPIN === false) {
            return false;
        }

        $proxy = Proxy::query()
            ->where('ip', $ip)
            ->first();
        if ($proxy !== null) {
            $message                   = 'use_of_proxy_not_permit';
            $array[ 'possible_fraud' ] = $message;
            $array[ 'type_fraud' ]     = 'use_of_proxy_not_permit';
            $array[ 'id_user' ]        = $user->usr_id;
            $array[ 'id_promo_code' ]  = $promotion->id;
            $array[ 'ip_user' ]        = $ip;
            $sendLogConsoleService     = new SendLogConsoleService();
            $sendLogConsoleService->execute( request(), 'promo-code', 'access', 'terminate', $array );
            throw new UnprocessableEntityHttpException( $message, null, Response::HTTP_BAD_REQUEST );
        }

        return true;
    }

    private function countryMatches($ip, $user, $promotion)
    {

        if ( ModelConst::CHECK_COUNTRY_FREE_SPIN === false ) {
            return false;
        }

        $idCountry = $user->country_id;

        $countryFromIP = $this->getUserCountry( $ip );
        $countryFromID = $this->getCountryIso( $idCountry );

        if ( $countryFromIP === null || $countryFromID === null ) {
            $message                   = 'not_exist_ip_or_country';
            $array[ 'possible_fraud' ] = $message;
            $array[ 'type_fraud' ]     = 'not_exist_ip_or_country';
            $array[ 'id_user' ]        = $user->usr_id;
            $array[ 'id_promo_code' ]  = $promotion->id;
            $array[ 'country_ip' ]     = $countryFromID;
            $array[ 'country_ID' ]     = $countryFromID;
            $array[ 'ip_user' ]        = $ip;
            $array[ 'id_country' ]     = $idCountry;

            $sendLogConsoleService = new \App\Core\Base\Services\SendLogConsoleService();
            $sendLogConsoleService->execute( request(), 'promo-code', 'access', 'terminate', $array );
            throw new UnprocessableEntityHttpException( $message, null, Response::HTTP_BAD_REQUEST );
        }

        if ($countryFromIP->countrySHORT === $countryFromID->country_Iso) {
            $message                   = 'wrong_country';
            $array[ 'possible_fraud' ] = $message;
            $array[ 'type_fraud' ]     = 'wrong_country';
            $sendLogConsoleService     = new \App\Core\Base\Services\SendLogConsoleService();
            $sendLogConsoleService->execute(request(), 'promo-code', 'access', 'terminate', $array);
            throw new UnprocessableEntityHttpException(
                $message, null, Response::HTTP_BAD_REQUEST
            );
        }
        return true;
    }

    private function duplicateAccountOk($user, $promotion)
    {
        if(ModelConst::CHECK_SCORE_FREE_SPIN === false){
            return false;
        }

        $columns = [ 'usr_id', 'usr_name', 'usr_lastname', 'usr_regdate', 'usr_phone', 'usr_password' ];

        $userByPhone = User::query()
            ->where('usr_phone', $user->user_phone)
            ->whereRaw('sys_id=1')
            ->whereRaw(
                'usr_id >= (SELECT usr_id FROM users WHERE usr_regdate>=DATE_SUB(NOW(), INTERVAL 3 MONTH) LIMIT 1)'
            )
            ->get($columns);

        $userByPass = User::query()
            ->where('usr_password', $user->usr_password)
            ->whereRaw('sys_id=1')
            ->whereRaw( 'usr_id >= (SELECT usr_id FROM users WHERE usr_regdate>=DATE_SUB(NOW(), INTERVAL 3 MONTH) LIMIT 1)' )
            ->get($columns);

        $merge = $userByPhone->merge($userByPass);
        $merge = $merge->unique('usr_id');
        if($merge->count() > 1){
            $message                   = 'user_duplicate';
            $array[ 'possible_fraud' ] = $message;
            $array[ 'type_fraud' ]     = 'user_duplicate '.$merge->count();
            $array[ 'id_user' ]        = $user->usr_id;
            $array[ 'id_promo_code' ]  = $promotion->id;
            $sendLogConsoleService     = new SendLogConsoleService();
            $sendLogConsoleService->execute(request(), 'promo-code', 'access', 'terminate', $array);
            throw new UnprocessableEntityHttpException(
                $message, null, Response::HTTP_BAD_REQUEST
            );
        }

        $idsUser = $merge->pluck('usr_id')->values();
        $checkDuplicate = $idsUser->duplicates();
        // TODO check logic

        if($checkDuplicate === false){

            $userByFirstNameAndLastName = User::query()
                ->whereIn('usr_id', $idsUser)
                ->where('usr_name', $user->usr_name)
                ->where('usr_lastname', $user->usr_lastname)
                ->whereRaw('sys_id=1')
                ->whereRaw( 'usr_id >= (SELECT usr_id FROM users WHERE usr_regdate>=DATE_SUB(NOW(), INTERVAL 3 MONTH) LIMIT 1)' )
                ->get($columns);

            if($userByFirstNameAndLastName->count() > 0){
                $message                   = 'user_duplicate';
                $message                   .= ' 2';
                $array[ 'possible_fraud' ] = $message;
                $array[ 'type_fraud' ]     = 'user_duplicate 2';
                $array[ 'id_user' ]        = $user->usr_id;
                $array[ 'id_promo_code' ]  = $promotion->id;
                $sendLogConsoleService     = new SendLogConsoleService();
                $sendLogConsoleService->execute(request(), 'promo-code', 'access', 'terminate', $array);
                throw new UnprocessableEntityHttpException(
                    $message, null, Response::HTTP_BAD_REQUEST
                );
            }
        }
        return true;
    }

    private function getUserCountry($ip)
    {
        $ip = str_replace(".", "", $ip);
        return Ip2Location::query()
            ->where('ipFROM', '<', $ip)
            ->where('ipTO', '>', $ip)
            ->first([ 'countrySHORT' ]);

    }

    private function getCountryIso($idCountry)
    {
        return Country::query()
            ->where('country_id', $idCountry)
            ->first([ 'country_Iso' ]);
    }
}
