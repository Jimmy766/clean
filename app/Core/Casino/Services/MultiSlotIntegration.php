<?php


    namespace App\Core\Casino\Services;

    use App\Core\Casino\Models\CasinoGame;
    use App\Core\Casino\Models\CasinoProviderConfig;
    use App\Core\Clients\Models\Client;
    use App\Core\Base\Traits\LogCache;
    use App\Core\Casino\Services\CasinoIntegration;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Log;

    class MultiSlotIntegration
    {
        use LogCache;
        /**
         * @param CasinoGame $game
         * @return string
         */
        private static function generateToken(CasinoGame $game){
            self::record_log_static('oryx', 'Begin request get MultiSlot Token');
            $url = str_replace( [ '{UserId}' , '{UserKey}' ] , [ Auth::user()->usr_id , md5(Auth::user()->usr_password)] , $game->provider->configs['API_TOKEN_URL']);
            self::record_log_static('oryx', 'Request: ', ['url'=>$url]);
            $response = CasinoIntegration::httpGetRequest($url);
            self::record_log_static('oryx', 'Response: ', [$response]);
            if(!empty($response)){
                if (strpos($response, 'Token') !== false) {
                    $tokenLong = explode('&', $response);
                    $token = explode('=', $tokenLong[0])[1];
                } else {// Error getting the MultiSlot token
                    self::record_log_static('oryx', "Error getting the MultiSlot token",['Game'=>$game->id,'User'=>Auth::user()->usr_id,'ErrorMessage'=>$response]);
                    self::record_log_static('errors', "Error getting the MultiSlot token", ['Game'=>$game->id,'User'=>Auth::user()->usr_id,'ErrorMessage'=>$response]);
                    // mail
                    return false;
                }
            }else{
                self::record_log_static('errors', "Error getting the MultiSlot token", ['Game'=>$game->id,'User'=>Auth::user()->usr_id,'ErrorMessage'=>$response]);
                // mail
                return false;
            }
            $game->saveToken($token);
            return $token;

        }

        /**
         * @param \App\Core\Casino\Models\CasinoGame $game
         * @param $game_mode
         * @param $language
         * @return string
         */
        public static function getUrl(CasinoGame $game, $game_mode,$language) {
            if ($game_mode == 'real') {
                if (!$token = self::generateToken($game))
                    return false;
            }
            $search = [
                '{token}',
                '{AccountId}',
                '{lang}',
                '{game_code}'
            ];
            $replace = [
                $game_mode == 'demo' ? '' : 'token=' . $token . '&',
                $game_mode == 'demo' ? '-1' : '1',
                $language,
                request()->is_mobile ? $game->code_mobile : $game->code,
            ];
            return $game->provider->configs['API_LAUNCH_GAME_URL'] . str_replace($search, $replace, $game->provider->configs['QSTRING']);
        }
    }
