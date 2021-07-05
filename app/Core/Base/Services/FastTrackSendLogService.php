<?php

namespace App\Core\Base\Services;

use App\Core\Rapi\Models\LogFasttrackApi;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * Class FastTrackSendLogService
 * @package App\Services
 */
class FastTrackSendLogService
{

    /**
     * @param $operation
     * @param $data
     * @return int|mixed
     */
    protected static function post($operation, $data)
    {
        if (env('APP_ENV', null) == 'local' || env('APP_ENV', null) == 'dev' ) {
            return Response::HTTP_OK;
        }

        [ $apiUrl, $apiKey ] = self::getUrlWithKey();

        $responseCode = self::runCurlToSendFastTrackInfo($apiKey, $apiUrl, $operation, $data);
        $data         = is_array($data) ? json_encode($data) : $data;

        $attributes = [
            "endpoint"  => $operation,
            "post_data" => $data,
            "response"  => $responseCode,
        ];
        LogFasttrackApi::create($attributes);

        return $responseCode;
    }

    /**
     * @return string[]
     */
    private static function getUrlWithKey()
    {
        $apiUrl = 'https://wintrillions-staging.ft-crm.com/';
        $apiKey = '4RN6qyheXKYzBA4heJAcApj2';

        if (env('APP_ENV', null) === 'prod') {
            $apiUrl = 'https://wintrillions.ft-crm.com/';
            $apiKey = 'm5SRPPJ8tHdnuiiyT4oF3NqFA6WqzNLY';
        }

        return [ $apiUrl, $apiKey ];
    }

    private static function runCurlToSendFastTrackInfo($apiKey, $apiUrl, $operation, $data)
    {
        $requestUrl = self::getRelativePathToUrl($operation);

        $client = new Client(
            [
                'base_uri'   => $apiUrl,
                'exceptions' => false,
                'curl'       => [
                    CURLOPT_SSL_VERIFYPEER => false,
                ],
            ]
        );

        $lengthData = is_array($data) ? strlen(json_encode($data)) : strlen($data);
        $headers    = [
            'Cache-Control'  => 'no-cache',
            'Content-Type'   => 'application/json',
            'Content-length' => $lengthData,
            'X-Api-Key'      => $apiKey,
        ];

        $formParams = is_array($data) ? $data : json_decode($data);
        $method     = 'POST';

        $formAndHeader = [ RequestOptions::JSON => $formParams, 'headers' => $headers, ];

        try {
            $response = $client->request($method, $requestUrl, $formAndHeader);
        }
        catch(Exception $exception) {
            $errorMessage = $exception->getMessage();
            LogType::error(__FILE__, __LINE__, $errorMessage, [
                'exception' => $exception,
                'usersId'   => Auth::id(),
            ]);
            return Response::HTTP_NOT_FOUND;
        }

        $codeRequest = $response->getStatusCode();

        $content = $response->getBody()
            ->getContents();

        $content = json_decode($content, true);

        if ($codeRequest != Response::HTTP_OK) {
            $content = json_encode($content);
            $content = 'FASTTRACK: ' . $content;
            $array[ 'content' ]                  = $content;
            $sendLogConsoleService = new SendLogConsoleService();
            $sendLogConsoleService->execute(request(), 'access', 'access', 'terminate', $array);
        }

        return $codeRequest;
    }

    private static function getRelativePathToUrl($operation)
    {
        $apiUrl = 'integration-api/api';
        switch ($operation) {
            case 'login':
            case 'lottery':
            case 'user':
                $apiUrl .= "/v2/integration/" . $operation;
                break;
            case 'balances':
                $apiUrl .= "/v1/integration/user/" . $operation;
                break;
            case 'bonus':
            case 'custom':
            case 'payment':
            case 'cart':
                $apiUrl .= "/v1/integration/" . $operation;
                break;
            case 'blocks':
                $apiUrl .= "/v2/integration/user/" . $operation;
                break;
            default:
                $apiUrl .= $operation;
        }

        return $apiUrl;
    }

}
