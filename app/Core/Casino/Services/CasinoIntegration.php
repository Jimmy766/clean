<?php

    namespace App\Core\Casino\Services;

    use App\Core\Casino\Models\CasinoGame;
    use App\Core\Casino\Models\CasinoProvider;
    use App\Core\Casino\Services\MultiSlotIntegration;
    use App\Core\Casino\Services\OryxIntegration;
    use App\Core\Casino\Services\RedTigerIntegration;
    use App\Core\Base\Services\LogType;
    use App\Core\Base\Traits\LogCache;
    use GuzzleHttp\Client as ClientHttp;
    use GuzzleHttp\Exception\ClientException;
    use Illuminate\Http\Response;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Log;

    class CasinoIntegration
    {
        use LogCache;

        /**
         * @param CasinoGame $game
         * @return array|bool
         */
        public static function canPlay(CasinoGame $game){

            $free_rounds = false;
            $bet_config = $game->bet_config_user_currency;

            if (empty($bet_config)){
                //Send mail error
                // log No Bet Config
                return false;
            }

            if ($game->user_game_balance < $bet_config->min_bet) { // Not enough balance to play the game
                if ($game->casino_provider_id == CasinoProvider::ORYX_CASINO_PROVIDER){ // Check for free rounds for ORYX
                    $free_rounds = OryxIntegration::checkPlayerHasFreeRounds($game);
                }
                if($game->casino_provider_id === CasinoProvider::REDTIGER_CASINO_PROVIDER){
                    return true;
                }
                if ($game->has_open_transaction || $free_rounds){
                    return true;
                }else{
                    return false;
                }
            }else{
                return true;
            }
        }

        /**
         * @param CasinoGame $game
         * @param $game_mode
         * @param $language
         * @return bool|string
         */
        public static function getUrl(CasinoGame $game, $game_mode,$language){
            self::record_log_static('oryx', 'Call Launch Game' . json_encode([
                    'GameId' => $game->id,
                    'GameCode:' => request()->is_mobile ? $game->code_mobile : $game->code,
                    'User' => Auth::user()->usr_id ?? '',
                    'Mode' => $game_mode,
                    'Language' => $language,
                    'isMobile' => request()->is_mobile ? 'Yes' : 'No'
                ]));

            if ($game->casino_provider_id == CasinoProvider::MULTISLOT_CASINO_PROVIDER){
                return MultiSlotIntegration::getUrl($game, $game_mode,$language);
            }elseif ($game->casino_provider_id == CasinoProvider::ORYX_CASINO_PROVIDER){
                return OryxIntegration::getUrl($game, $game_mode,$language);
            }elseif ($game->casino_provider_id == CasinoProvider::REDTIGER_CASINO_PROVIDER){
                return RedTigerIntegration::getUrl($game, $game_mode,$language);
            }else{
                return false;
            }
        }

        /**
         * Make GET request
         * @param $url
         * @param array $options
         * @return bool|mixed
         * @throws \GuzzleHttp\Exception\GuzzleException
         */
        public static function httpGetRequest($url, $options = []) {

            $httpClient = new ClientHttp();
            try {
                $response = $httpClient->get($url, $options);
                return $response->getBody()->getContents();
            } catch (ClientException $exception) {
                $errorMessage = $exception->getMessage();
                LogType::error(__FILE__, __LINE__, $errorMessage, [
                    'exception' => $exception,
                    'usersId'   => Auth::id(),
                ]);
                return $exception->getResponse()->getBody()->getContents();
            }
        }

    }
