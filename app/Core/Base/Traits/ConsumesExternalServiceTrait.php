<?php

namespace App\Core\Base\Traits;

use GuzzleHttp\Client;

/**
 * Trait consumesExternalService
 * @package App\Traits
 */
trait ConsumesExternalServiceTrait
{

    public function performRequest(
        $baseUri,
        $method,
        $requestUrl = '',
        $formParams = [],
        $headers = []
    ) {
        $client = new Client(
            [
                'base_uri' => $baseUri,
                'curl'     => [
                    CURLOPT_SSL_VERIFYPEER => false,
                ],
            ]
        );

        $headers[ 'cache-control' ] = 'no-cache';

        $formAndHeader = [
            'form_params' => $formParams,
            'headers'     => $headers,
        ];

        $response = $client->request($method, $requestUrl, $formAndHeader);

        $content = $response->getBody()->getContents();

        return json_decode($content, true);

    }

}
