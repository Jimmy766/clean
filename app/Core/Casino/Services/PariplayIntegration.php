<?php

    namespace App\Core\Casino\Services;


    use App\Core\Clients\Models\Client;
    use App\Core\ScratchCards\Notifications\AlertScratchCard;
    use App\Core\ScratchCards\Models\ScratchCard;
    use App\Core\ScratchCards\Models\ScratchCardGameBonus;
    use App\Core\Base\Traits\LogCache;
    use App\Core\Users\Models\User;
    use GuzzleHttp\Client as ClientHttp;
    use GuzzleHttp\Exception\ClientException;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\Mail;

    class PariplayIntegration
    {
        use LogCache;
        const DEFAULT_FINANCIAL_MODE = 1;
        const API_URL = 'https://hubgames.pariplaygames.com/api/';
        const API_USER = 'trillonario';
        const API_PASS = 'f3aZ1kF3aV';
        const API_GENERAL_ERROR = 900;

        /**
         * @param \App\Core\ScratchCards\Models\ScratchCard $scratch_card
         * @param bool                                      $is_mobile
         * @param string                                    $language
         * @param string                                    $game_mode
         * @param null                                      $user
         * @return mixed|\Psr\Http\Message\ResponseInterface|\stdClass
         */
        public static function getResponsePlayUrl(ScratchCard $scratch_card, bool $is_mobile, string $language, string $game_mode = 'demo', $user = null) {
            self::record_log_static('pariplay', 'Begin request play');
            self::record_log_static('pariplay', "Scratch card: {$scratch_card->id}, Languange: {$language}, Game mode: {$game_mode}, Is " . ($is_mobile ? '' : 'not ') . 'call mobile');
            $client = new ClientHttp(['base_uri' => self::API_URL]);
            $request_params = self::getPlayRequestParams($scratch_card, $is_mobile, $language, $game_mode, $user);
            self::record_log_static('pariplay', 'Request: ' . json_encode($request_params));
            try {
                $response = $client->post($game_mode == 'demo' ? 'LaunchDemoGame' : 'LaunchGame', [
                        'body' => json_encode($request_params, 1),
                        'headers' => [
                            'Acept' => 'application/json',
                            'Content-Type' => 'application/json'
                        ]
                    ]
                );
                $response = json_decode($response->getBody()->getContents());
                self::record_log_static('pariplay', 'Response: ' . json_encode($response));
                if (isset($response->Error, $response->Error->ErrorCode)) {
                    $error = self::getError($response->Error);
                    self::report($scratch_card, $error, $request_params, $user, ['mobile' => $is_mobile, 'language' => $language, 'game_mode' => $game_mode]);
                    return $error;
                }
                $response->FinancialMode = $request_params[ 'FinancialMode' ];
                return $response;
            } catch (ClientException $client_exception) {
                $error = self::getError(self::API_GENERAL_ERROR);
                self::report($scratch_card, $error, $request_params, $user, ['mobile' => $is_mobile, 'language' => $language, 'game_mode' => $game_mode, 'exception' => $client_exception->getMessage()]);
                return $error;
            }
        }

        /**
         * @param ScratchCard $scratch_card
         * @param bool $is_mobile
         * @param string $language
         * @param string $game_mode
         * @param $user
         * @return array
         */
        private static function getPlayRequestParams(ScratchCard $scratch_card, bool $is_mobile, string $language, string $game_mode, $user): array {
            $base_play_request = self::getBasePlayRequestParams($scratch_card, $is_mobile, $language);

            return $game_mode == 'demo' ? $base_play_request : array_merge($base_play_request, self::getRealRequestParams($user)
            );
        }

        /**
         * @param ScratchCard $scratch_card
         * @param bool $is_mobile
         * @param string $language
         * @return array
         */
        private static function getBasePlayRequestParams(ScratchCard $scratch_card, bool $is_mobile, string $language): array {
            $request = [
                'LanguageCode' => self::getLanguageFromLanguages($scratch_card->languages, $language),
                //optionals from documentation but really is mandatory
                'PlayerIP' => request()->user_ip,
                //optionals from documentation
                'FinancialMode' => PariplayIntegration::DEFAULT_FINANCIAL_MODE,
                'EcommerceTicketPrice' => (float)$scratch_card->ticket_price->price,
            ];
            return array_merge($request, self::getBaseRequestParams($scratch_card, $is_mobile));
        }

        /**
         * @param array $languages
         * @param string $language
         * @return string
         */
        private static function getLanguageFromLanguages(array $languages, string $language): string {
            $default_language = 'en-US';
            foreach ($languages as $lang) {
                if (strpos($lang, $language) !== false) {
                    $default_language = $lang;
                }
            }
            return $default_language;
        }

        /**
         * @param \App\Core\ScratchCards\Models\ScratchCard $scratch_card
         * @param bool                                      $is_mobile
         * @return array
         */
        private static function getBaseRequestParams(ScratchCard $scratch_card, bool $is_mobile): array {
            $scratch_card_subscription = request()->route()->scratch_card_subscription;
            $request = [
                'GameCode' => $scratch_card->getGameCode($is_mobile),
                'CurrencyCode' => $scratch_card_subscription ? $scratch_card_subscription->cart_subscription->cart->crt_currency : request()->country_currency,
                'Account' => [
                    'UserName' => self::API_USER,
                    'Password' => self::API_PASS
                ]
            ];
            return $request;
        }

        /**
         * @param User $user
         * @return array
         */
        private static function getRealRequestParams($user): array {
            return [
                "PlayerId" => $user->usr_id,
                "CountryCode" => $user->country->country_Iso,
            ];
        }

        /**
         * @param integer $Error
         * @return \stdClass
         */
        private static function getError($Error): \stdClass {
            $error_code = is_int($Error) ? $Error : $Error->ErrorCode;
            $error = new \stdClass();
            switch ($error_code) {
                case 1:
                    $error->name = 'DemoModeNotAvailable';
                    $error->error = isset($Error->Description) && !empty(isset($Error->Description)) ? $Error->Description : 'The game doesn\'t support Demo mode (usually LiveDealer games).';
                    break;
                case 2:
                    $error->name = 'FreeRoundsNotAvailable';
                    $error->error = isset($Error->Description) && !empty(isset($Error->Description)) ? $Error->Description :  'Free Round is not available.';
                    break;
                case 3:
                    $error->name = 'AuthenticationFailed';
                    $error->error = isset($Error->Description) && !empty(isset($Error->Description)) ? $Error->Description : 'The credentials provided in the API are wrong.';
                    break;
                case 4:
                    $error->name = 'InvalidGame';
                    $error->error = isset($Error->Description) && !empty(isset($Error->Description)) ? $Error->Description : 'Game was not found.';
                    break;
                case 5:
                    $error->name = 'BadRequest';
                    $error->error = isset($Error->Description) && !empty(isset($Error->Description)) ? $Error->Description : 'Bad request.';
                    break;
                case 6:
                    $error->name = 'InvalidCurrencyCode';
                    $error->error = isset($Error->Description) && !empty(isset($Error->Description)) ? $Error->Description : 'Currency code was not found.';
                    break;
                case 7:
                    $error->name = 'InvalidCountryCode';
                    $error->error = isset($Error->Description) && !empty(isset($Error->Description)) ? $Error->Description : 'Country code was not found.';
                    break;
                case 8:
                    $error->name = 'InvalidLanguageCode';
                    $error->error = isset($Error->Description) && !empty(isset($Error->Description)) ? $Error->Description : 'Language code was not found.';
                    break;
                case 9:
                    $error->name = 'IpRestricted';
                    $error->error = isset($Error->Description) && !empty(isset($Error->Description)) ? $Error->Description :  'Player IP address is restricted for playing in real money.';
                    break;
                case 10:
                    $error->name = 'InvalidTable';
                    $error->error = isset($Error->Description) && !empty(isset($Error->Description)) ? $Error->Description : 'Game table was not found.';
                    break;
                case 14:
                    $error->name = 'GameNotAvailableDueToPlayerJurisdiction';
                    $error->error = isset($Error->Description) && !empty(isset($Error->Description)) ? $Error->Description : 'Player can run the game due to player jurisdiction.';
                    break;
                case 900:
                    $error->name = 'GeneralError';
                    $error->error = isset($Error->Description) && !empty(isset($Error->Description)) ? $Error->Description : 'An unhandled error occurred.';
                    break;
                default:
                    $error->name = 'InvalidErrorCode';
                    $error->error = isset($Error->Description) && !empty(isset($Error->Description)) ? $Error->Description : 'Error code not known or invalid.';
                    break;
            }
            self::record_log_static('pariplay', 'Error occurred: ' . json_encode($error));

            return $error;
        }

        private static function report(ScratchCard $scratch_card, \stdClass $error, array $request_params, $user = null, $extra = []) {
            $site = Client::find(request()[ 'oauth_client_id' ])->site;
            retry(5, function () use ($scratch_card, $error, $site, $request_params, $user, $extra) {
            Mail::to(env('MAIL_ALERT_SCRATCH', 'alerts_frontend@trillonario.com'))->send(new AlertScratchCard($scratch_card, $error, $site, $request_params, $user, $extra));
            }, 100);
        }

        /**
         * @param \App\Core\ScratchCards\Models\ScratchCardGameBonus $scratch_card_game_bonus
         * @param bool                                               $is_mobile
         * @param User                                               $user
         * @return mixed|\Psr\Http\Message\ResponseInterface|\stdClass
         */
        public static function getBonusId(ScratchCardGameBonus $scratch_card_game_bonus, bool $is_mobile, $user) {
            self::record_log_static('pariplay', 'Begin request free rounds');
            self::record_log_static('pariplay', "Scratch card: {$scratch_card_game_bonus->scratches_id}, Free rounds: {$scratch_card_game_bonus->free_rounds}, Is " . ($is_mobile ? '' : 'not ') . 'call mobile');
            $client = new ClientHttp(['base_uri' => self::API_URL]);
            $request_params = self::getBonusRequestParams($scratch_card_game_bonus, $is_mobile, $user);
            self::record_log_static('pariplay', 'Request: ' . json_encode($request_params));
            try {
                $response = $client->post('FreeRounds/Add', [
                        'body' => json_encode($request_params, 1),
                        'headers' => [
                            'Acept' => 'application/json',
                            'Content-Type' => 'application/json'
                        ]
                    ]
                );
                $response = json_decode($response->getBody()->getContents());
                self::record_log_static('pariplay', 'Response: ' . json_encode($response));
                if (isset($response->Error, $response->Error->ErrorCode)) {
                    $error = self::getError($response->Error->ErrorCode);
                    self::report($scratch_card_game_bonus->scratch_card, $error, $request_params, $user, ['mobile' => $is_mobile, 'sub_id' => $scratch_card_game_bonus->sub_id]);
                    return $error;
                }
                return $response;
            } catch (ClientException $client_exception) {
                $error = self::getError(self::API_GENERAL_ERROR);
                self::report($scratch_card_game_bonus->scratch_card, $error, $request_params, $user, ['mobile' => $is_mobile, 'sub_id' => $scratch_card_game_bonus->sub_id, 'exception' => $client_exception->getMessage()]);
                return $error;
            }
        }

        /**
         * @param \App\Core\ScratchCards\Models\ScratchCardGameBonus $scratch_card_game_bonus
         * @param bool                                               $is_mobile
         * @param User                                               $user
         * @return array
         */
        private static function getBonusRequestParams(ScratchCardGameBonus $scratch_card_game_bonus, bool $is_mobile, $user): array {
            $request = [
                'NumberFreeRounds' => $scratch_card_game_bonus->free_rounds,
                'ExpirationDate' => $scratch_card_game_bonus->expiration_date->format('Y-m-d\TH:i:s\Z'),
            ];
            return array_merge($request, self::getBaseRequestParams($scratch_card_game_bonus->scratch_card, $is_mobile), self::getRealRequestParams($user));
        }

        /**
         * @param \stdClass $response
         * @return \stdClass
         */
        public static function prepareResponse(\stdClass $response): \stdClass {
            if (isset($response->FinancialMode)) {
                unset($response->FinancialMode);
            }
            if (isset($response->Token)) {
                unset($response->Token);
            }
            return $response;
        }

    }
