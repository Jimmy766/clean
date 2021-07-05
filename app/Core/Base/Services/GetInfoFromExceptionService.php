<?php

namespace App\Core\Base\Services;

use Illuminate\Support\Facades\Auth;

class GetInfoFromExceptionService
{

    /**
     * @param       $request
     * @param       $exception
     * @param array $extraValues
     * @return array
     */
    public static function execute($request, $exception, $extraValues = []): array
    {
        $infoEndpoint = [
            'message_error' => $exception->getMessage(),
            'uri'           => $request->getRequestUri(),
            'method'        => $request->method(),
            'ip_client'     => $request->getClientIp(),
            'user'          => Auth::id(),
        ];

        $infoEndpoint[ 'environment' ]     = env('APP_ENV');
        $infoEndpoint[ 'code' ]            = $exception->getCode();
        $infoEndpoint[ 'line' ]            = $exception->getLine();
        $infoEndpoint[ 'file' ]            = $exception->getFile();
        $infoEndpoint[ 'from' ]            = $request->server->get('REMOTE_ADDR');
        $infoEndpoint[ 'count_errors' ]    = 1;
        $infoEndpoint[ 'charset_general' ] = GetCharsetGeneralServices::execute();

        $headers = GetAllValuesFromHeaderService::execute($request);

        $infoEndpoint = array_merge($infoEndpoint, $headers->toArray());

        $infoEndpoint = array_merge($infoEndpoint, $extraValues);

        if (array_key_exists('authorization', $infoEndpoint)) {
            $authorization = $infoEndpoint[ 'authorization' ];
            unset($infoEndpoint[ 'authorization' ]);
            $infoEndpoint[ 'authorization' ] = $authorization;
        }

        return $infoEndpoint;
    }
}
