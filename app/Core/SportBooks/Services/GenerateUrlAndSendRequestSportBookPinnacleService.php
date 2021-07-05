<?php

namespace App\Core\SportBooks\Services;

use App\Core\Base\Traits\ConsumesExternalServiceTrait;
use App\Core\SportBooks\Services\SendRequestSportBookService;
use App\Core\SportBooks\Models\SportBooksProviderConfig;
use App\Core\Base\Traits\LogCache;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class GenerateUrlAndSendRequestSportBookPinnacleService
 * @package App\Services
 */
class GenerateUrlAndSendRequestSportBookPinnacleService
{

    use LogCache;
    use ConsumesExternalServiceTrait;

    /**
     * @var SendRequestSportBookService
     */
    private $sendRequestSportBookService;

    public function __construct(SendRequestSportBookService $sendRequestSportBookService)
    {
        $this->sendRequestSportBookService = $sendRequestSportBookService;
    }

    /**
     * @param      $providersConfig
     * @param      $language
     * @param      $currency
     * @param      $response
     * @param      $user
     * @param null $requestUrl
     * @param      $site
     * @return mixed
     */
    public function execute(
        $providersConfig,
        $language,
        $currency,
        $response,
        $user,
        $requestUrl = null,
        $site
    ) {
        $sportBooksDefaultCount = $providersConfig->where( 'sport_books_provider_id', SportBooksProviderConfig::SPORT_BOOKS_PINNACLE )->count();

        if ($sportBooksDefaultCount > 0) {
            $this->sendInfoLaunchGame($language, $user);
            $response = $this->generateUrlAndSendRequest(
                $providersConfig, $language, $currency, $user, $requestUrl, $site
            );
        }
        return $response;
    }

    /**
     * @param $language
     * @param $user
     */
    private function sendInfoLaunchGame($language, $user): void
    {
        $arrayInfoLaunchGame = [
            'User'     => $user->usr_id ?? '',
            'Language' => $language,
        ];

        $infoLaunchGame = json_encode($arrayInfoLaunchGame);

        self::record_log_static(
            'sportbooks',
            "Call Launch Game {$infoLaunchGame}"
        );

    }

    /**
     * @param      $providersConfig
     * @param      $language
     * @param      $currency
     * @param      $user
     * @param null $requestUrl
     * @param      $site
     * @return bool|mixed
     */
    private function generateUrlAndSendRequest(
        $providersConfig,
        $language,
        $currency,
        $user,
        $requestUrl = null,
        $site
    ) {
        [ $baseUri, $requestUrl ] = $this->getUrlRequest(
            $providersConfig,
            $currency,
            $language,
            $user,
            $requestUrl
        );

        [ $agentKey, $secretKey ] = $this->getAgentsAndSecretKey(
            $providersConfig
        );

        $currencyAgentCode = $this->getAgentCodeByCurrency(
            $currency,
            $providersConfig
        );

        $idSportBook = SportBooksProviderConfig::SPORT_BOOKS_PINNACLE;
        $idUser = \Auth::id();
        return $this->sendRequestSportBookService->execute(
            $baseUri, $requestUrl, $currencyAgentCode, $agentKey, $secretKey, $site, $idUser, $idSportBook
        );

    }

    /**
     * @param      $providersConfig
     * @param      $currency
     * @param      $language
     * @param      $user
     * @param null $requestUrl
     * @return array
     */
    public function getUrlRequest($providersConfig, $currency, $language, $user, $requestUrl =null ): array
    {
        $requestUrl = is_null($requestUrl) ? 'REQUEST_URL' : $requestUrl;
        $baseUri       = $providersConfig->where('key', 'BASE_URI')->firstFromCache();
        $requestUrl    = $providersConfig->where('key', $requestUrl)->firstFromCache();
        $parametersUrl = $providersConfig->where('key', 'PARAMETERS_URL')
            ->firstFromCache();
        $typeView      = $providersConfig->where('key', 'TYPE_VIEW')->firstFromCache();

        if ($baseUri === null || $requestUrl === null || $parametersUrl === null || $parametersUrl === null) {
            $message = __('lang.not_url_sport_books_service_complete');
            throw new UnprocessableEntityHttpException($message, null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $baseUri       = $baseUri->param;
        $requestUrl    = $requestUrl->param;
        $parametersUrl = $parametersUrl->param;
        $typeView      = $typeView->param;

        $userId  = 'TRILLONARIO_' . $user->usr_id;
        $replace = [ $userId, $typeView, $language ];
        $search  = [ '{loginId}', '{view}', '{locale}' ];

        //replace parameters values at url
        $parametersUrl = str_replace($search, $replace, $parametersUrl);

        $requestUrl .= "" . $parametersUrl;

        return [ $baseUri, $requestUrl ];
    }

    /**
     * @param $providersConfig
     * @return array
     */
    public function getAgentsAndSecretKey($providersConfig): array
    {
        $agentKey  = $providersConfig->where('key', 'AGENT_KEY')->firstFromCache();
        $secretKey = $providersConfig->where('key', 'SECRET_KEY')->firstFromCache();

        if ($agentKey === null || $secretKey === null) {
            $message = __('lang.not_sport_books_agent_not_key');
            throw new UnprocessableEntityHttpException($message, null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $agentKey  = $agentKey->param;
        $secretKey = $secretKey->param;

        return [ $agentKey, $secretKey ];
    }

    /**
     * @param $currency
     * @param $providersConfig
     * @return mixed
     */
    public function getAgentCodeByCurrency($currency, $providersConfig)
    {
        $currencyAgentCode = null;

        $currency = strtoupper($currency);
        $currency .= '_AGENT_CODE';

        $currencyAgentCode = $providersConfig->where('key', $currency)->firstFromCache();

        if ($currencyAgentCode === null) {
            $currencyAgentCode = $providersConfig->where(
                'key',
                'USD_AGENT_CODE'
            )->firstFromCache();
        }

        if ($currencyAgentCode === null) {
            $message = __('lang.not_sport_books_agent_code_service');
            throw new UnprocessableEntityHttpException($message, null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $currencyAgentCode->param;
    }
}
