<?php


    namespace App\Core\Casino\Services;

    use App\Core\Casino\Models\CasinoGame;
    use App\Core\Clients\Models\Client;
    use App\Core\Base\Traits\LogCache;
    use App\Core\Casino\Services\CasinoIntegration;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Log;

    class OryxIntegration
    {
        use LogCache;

        const LANGS = [
            'en'=> 'ENG',
            'es'=> 'SPA',
            'pt'=> 'POR',
            'it'=> 'ITA',
            'fr'=> 'FRA',
            'de'=> 'DEU',
            'ru'=> 'RUS',
            'cn'=> 'CHI',
            'tw'=> 'CHI',
            'pl'=> 'POL'
        ];

        /**
         * @param CasinoGame $game
         * @return array|bool
         * @throws \GuzzleHttp\Exception\GuzzleException
         */
        public static function checkPlayerHasFreeRounds(CasinoGame $game){

            $url = str_replace('{playerId}',Auth::user()->usr_id,$game->provider->configs['API_FREE_ROUNDS_URL']);// playerId
            $options =[
                'auth' => [
                    $game->provider->configs['API_FREE_ROUNDS_USER'],
                    $game->provider->configs['API_FREE_ROUNDS_PASSWORD']
                ]
            ];
            self::record_log_static('oryx', 'Begin request free rounds');
            self::record_log_static('oryx', 'Request: ', ['url'=>$url]);
            $response = CasinoIntegration::httpGetRequest($url,$options);
            self::record_log_static('oryx', 'Response: ', json_decode($response,true));
            $response = json_decode($response);
            if (isset($response->error)){
                self::record_log_static('oryx', "PlayerHasFreeRounds",['Game'=>$game->id,'User'=>Auth::user()->usr_id,'ErrorMessage'=>$response]);
                return false;
            }

            $freeRounds = $response->free_rounds;
            $founded = false;
            if(count($freeRounds)) {
                foreach ($freeRounds as $item) {
                    if (array_search(request()->is_mobile?$game->code_mobile:$game->code, $item->games) !== false) {
                        $founded = true;
                        break;
                    }
                }
            }
            return $founded;
        }

        /**
         * @param \App\Core\Casino\Models\CasinoGame $game
         * @return string
         */
        private static function generateToken(CasinoGame $game){
            //Generate a random string.
            $random = openssl_random_pseudo_bytes(10);
            //Convert the binary data into hexadecimal representation.
            $token = bin2hex($random . Auth::user()->usr_id);

            $game->saveToken($token);

            return $token;

        }

        /**
         * @param \App\Core\Casino\Models\CasinoGame $game
         * @param $game_mode
         * @param $language
         * @return string
         */
        public static function getUrl(CasinoGame $game, $game_mode,$language){
            if ($game_mode=='real'){
                $token = self::generateToken($game);
            }
            $search = [
                '{lang}',
                '{token}',
                '{playMode}',
                '{wallet}',
                '{game_code}'
            ];
            $replace = [
                self::LANGS[$language],
                $game_mode == 'demo' ? '' : 'token=' . $token . '&',
                $game_mode == 'demo' ? 'FUN' : 'REAL',
                $game->provider->configs['WALLET_'.Client::where('id', request()['oauth_client_id'])->first()->site->system->sys_id],
                request()->is_mobile?$game->code_mobile:$game->code,
            ];
            if ($game_mode=='real'){
                $search = array_merge($search, [ '{username}' , '{sessionId}' ]);
                $replace = array_merge($replace, [ Auth::user()->usr_name . ' ' . Auth::user()->usr_lastname , md5(Auth::user()->usr_id) ]);
            }

            $QSTRING = str_replace($search,$replace,( $game_mode == 'demo' ? $game->provider->configs['QSTRING'] : $game->provider->configs['QSTRING'] . $game->provider->configs['QSTRING_2']));
            return $game->provider->configs['API_LAUNCH_GAME_URL'].$QSTRING;
        }

    }
