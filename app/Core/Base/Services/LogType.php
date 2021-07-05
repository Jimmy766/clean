<?php

namespace App\Core\Base\Services;

use App\Core\Base\Classes\ModelConst;
use App\Core\Base\Traits\ErrorNotificationTrait;
use Illuminate\Support\Facades\Log;

/**
 * Class LogType
 * @package App\Services\Utils
 */
class LogType
{

    use ErrorNotificationTrait;

    const CHANNEL_BACKEND = 'backend';
    const CHANNEL_IMPORT  = 'import';
    const CHANNEL_EXPORT  = 'export';
    const CHANNEL_MAILER  = 'mailer';
    const CHANNEL_METRICS = 'metrics';

    static public function error($file, $line, $message, $context = [], $channel = self::CHANNEL_BACKEND, $key = null)
    {
        $exception = $context[ 'exception' ];
        if (config('app.debug') === false) {
            Sentry::captureException($exception);
        }

        $extraValues[ 'file' ] = $file;
        $extraValues[ 'line' ] = $line;
        $request = request();
        $infoEndpoint = GetInfoFromExceptionService::execute($request, $exception,$extraValues);
        $sendLogConsoleService = new SendLogConsoleService();
        $sendLogConsoleService->execute(
            $request,
            'errors',
            'errors',
            'error:' . $exception->getMessage(),
            $infoEndpoint
        );
        if(env('APP_ENV', null) !== 'dev' && env('APP_ENV', null) !== 'local'){
            ( new LogType() )->sendErrorNotification(
                $infoEndpoint,
                ModelConst::TOTAL_EXCEPTION_ERROR
            );
        }

    }

    public static function info($message, $context = [], $channel = self::CHANNEL_BACKEND, $key = null)
    {
        if ( !is_null($key)) {
            $context[ 'log-tracking-key' ] = $key;
        }

        Log::channel($channel)->info($message, $context);
    }

    public static function debug($file, $line, $message, $context = [], $channel = self::CHANNEL_BACKEND, $key = null)
    {
        $context[ 'file' ] = $file;
        $context[ 'line' ] = $line;

        if ( !is_null($key)) {
            $context[ 'log-tracking-key' ] = $key;
        }

        Log::channel($channel)->debug($message, $context);
    }

    public static function alert($file, $line, $message, $context = [], $channel = self::CHANNEL_BACKEND, $key = null)
    {
        $context[ 'file' ] = $file;
        $context[ 'line' ] = $line;

        if ( !is_null($key)) {
            $context[ 'log-tracking-key' ] = $key;
        }

        Log::channel($channel)->alert($message, $context);
    }
}
