<?php

namespace App\Core\SportBooks\Services;

use App\Core\Base\Services\PHPCipherService;
use App\Core\Base\Traits\CacheUtilsTraits;
use App\Core\Base\Traits\ConsumesExternalServiceTrait;
use App\Core\SportBooks\Models\SportbookToken;
use App\Core\Base\Traits\LogCache;

/**
 * Class SendRequestSportBookService
 * @package App\Services
 */
class SendRequestSportBookService
{

    use LogCache;
    use ConsumesExternalServiceTrait;
    use CacheUtilsTraits;

    private $generated;

    /**
     * @param $baseUri
     * @param $requestUrl
     * @param $currencyAgentCode
     * @param $agentKey
     * @param $secretKey
     * @param $site
     * @param $idUser
     * @param $idSportBook
     * @return mixed
     */
    public function execute(
        $baseUri,
        $requestUrl,
        $currencyAgentCode,
        $agentKey,
        $secretKey,
        $site,
        $idUser,
        $idSportBook
    ) {
        self::record_log_static(
            'sport-books',
            'Begin request get MultiSlot Token'
        );
        self::record_log_static(
            'sport-books',
            'Request: ',
            [
                'url' => $baseUri . "" . $requestUrl,
            ]
        );
        $header   = $this->makeHeaderHeader(
            $currencyAgentCode,
            $agentKey,
            $secretKey
        );

        $token = $header['token'];
        $userCode = $header['userCode'];

        $response = $this->performRequest(
            $baseUri,
            'GET',
            $requestUrl,
            [],
            $header
        );

        self::record_log_static('sport-books', 'Response: ', [ $response ]);

        if (array_key_exists('loginUrl', $response)) {
            $url = $response[ 'loginUrl' ];
            $userCodeResponse = $response[ 'userCode' ];
            $this->saveNewUserSportbook($idUser, $token, $userCodeResponse, $site, $idSportBook);
            return $this->getReplaceApiUrl($url, $site);
        }

        return false;
    }
    public function getReplaceApiUrl($url,$site){
        $search = 'ooe0xga.tender88.com';
        $replace = '';
        switch ($site) {
            case 1:
            case 999:
            case 994:
            case 993:
            case 992:
                $replace = "pinnacle.trillonario.com";
                break;
            case 997:
                $replace = "pinnacle.trillonario.es";
                break;
            case 1088:
                $replace = "pinnacle.wintrillions.ca";
                break;
            case 1080:
            case 1077:
            case 1076:
                $replace = "pinnacle.trillonario.net";
                break;
            default:
                $replace = "pinnacle.wintrillions.com";
                break;
        }
        return str_replace($search, $replace, $url);
    }

    public function saveNewUserSportbook($idUser, $token, $userCodeResponse, $site, $idSportbook)
    {
        $tag = [ SportbookToken::TAG_CACHE_MODEL, ];
        $sportBook = SportbookToken::query()
            ->where('usr_id', $idUser)
            ->firstFromCache([ '*' ], $tag);
        if (is_null($sportBook)) {
            $sportBookToken                = new SportbookToken();
            $sportBookToken->usr_id        = $idUser;
            $sportBookToken->token         = $token;
            $sportBookToken->site_id       = $site;
            $sportBookToken->sportsbook_id = $idSportbook;
            $sportBookToken->user_code     = $userCodeResponse;
            $sportBookToken->save();
            $this->forgetCacheByTag($tag);
        }
    }

    /**
     * @param $agentCode
     * @param $agentKey
     * @param $secretKey
     * @return array
     */
    private function makeHeaderHeader(
        $agentCode,
        $agentKey,
        $secretKey
    ): array {
        $token = $this->token($agentCode, $agentKey, $secretKey);
        return [
            "token"    => $token,
            "userCode" => $agentCode,
        ];
    }

    /**
     * @param $agentCode
     * @param $agentKey
     * @param $secretKey
     * @return string
     */
    private function token($agentCode, $agentKey, $secretKey): string
    {
        $token       = '';
        $dbtimestamp = strtotime($this->generated);
        $time        = time();
        if ($time - $dbtimestamp > 15 * 60) {
            $cipher = new PHPCipherService();
            $token  = $cipher->generateToken(
                $agentCode,
                $agentKey,
                $secretKey
            );

            $this->generated = $time;
        }
        return $token;
    }

}
