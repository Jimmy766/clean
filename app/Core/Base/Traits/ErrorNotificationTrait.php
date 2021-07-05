<?php

namespace App\Core\Base\Traits;

use App\Core\Base\Classes\ModelConst;
use App\Core\Base\Notifications\ErrorSlackNotification;
use App\Core\Base\Services\GetInfoFromExceptionService;
use App\Core\Base\Services\SendLogConsoleService;
use App\Core\Rapi\Notifications\ExceptionEmailNotification;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;

trait ErrorNotificationTrait
{
    public function sendErrorNotification($infoEndpoint, $nameException, $type = null): void
    {
        $infoEndpoint['type_notification'] = $type;
        [ $directNotification, $infoEndpoint ] = $this->groupExceptionNotification($infoEndpoint, $nameException);
        $this->sendSlackNotification($infoEndpoint, $type, $directNotification);
        $this->sendMailNotification($infoEndpoint, $nameException, $type, $directNotification);
    }

    public function groupExceptionNotification($infoEndpoint, $nameException): array
    {
        try {
            $directNotification = true;
            $tag                = ModelConst::CACHE_NAME_EXCEPTION_NOTIFICATION;
            $nameCache          = 'rapi_errors_' . $nameException;
            $dataFromCache      = Cache::tags($tag)
                ->get($nameCache);
            if ($dataFromCache === null) {
                $infoEndpoint[ 'count_errors' ] = 1;
            }
            if ($dataFromCache !== null) {
                $messageException          = array_key_exists('message_error', $infoEndpoint)
                    ? $infoEndpoint[ 'message_error' ] : 'empty error';
                $messageExceptionFromCache = array_key_exists('message_error', $dataFromCache)
                    ? $dataFromCache[ 'message_error' ] : 'empty message error';
                if ($messageException === $messageExceptionFromCache) {
                    $countErrors                    = array_key_exists('count_errors', $dataFromCache)
                        ? $dataFromCache[ 'count_errors' ] : 0;
                    $infoEndpoint[ 'count_errors' ] = $countErrors + 1;
                    $directNotification             = false;
                }
            }

            $time = ModelConst::CACHE_TIME_TEN_MINUTES;
            Cache::tags($tag)
                ->put($nameCache, $infoEndpoint, $time);
            return [ $directNotification, $infoEndpoint ];
        }
        catch(Exception $exception) {
            $request = request();
            $infoEndpoint = GetInfoFromExceptionService::execute($request, $exception);
            $sendLogConsoleService = new SendLogConsoleService();
            $sendLogConsoleService->execute(
                $request,
                'errors',
                'errors',
                'error:' . $exception->getMessage(),
                $infoEndpoint
            );
            return [ $directNotification, $infoEndpoint ];
        }
    }

    /**
     * @param $infoEndpoint
     * @param $type
     * @param $directNotification
     * @return bool|null
     */
    public function sendSlackNotification($infoEndpoint, $type, $directNotification): ?bool
    {
        if ($directNotification === false) {
            return false;
        }
        if ($type !== null && $type !== 'slack') {
            return false;
        }
        try {
            Notification::route('slack', env('LOG_SLACK_WEBHOOK_URL'))
                ->notify(new ErrorSlackNotification($infoEndpoint));
        }
        catch(Exception $exception) {
            $request = request();
            $infoEndpoint = GetInfoFromExceptionService::execute($request, $exception);
            $sendLogConsoleService = new SendLogConsoleService();
            $sendLogConsoleService->execute(
                $request,
                'errors',
                'errors',
                'error:' . $exception->getMessage(),
                $infoEndpoint
            );
        }

        return false;
    }

    /**
     * @param $infoEndpoint
     * @param $nameException
     * @param $type
     * @param $directNotification
     * @return false|string
     */
    public function sendMailNotification(
        $infoEndpoint,
        $nameException,
        $type,
        $directNotification
    ) {
        if ($directNotification === false) {
            return false;
        }

        if ($type !== null && $type !== 'mail') {
            return false;
        }

        if (config('app.debug') === false) {
            Config::set('mail.from.address', 'alerts@rapi-' . config('app.env') . '.trillonario.com');
            Config::set('mail.from.name', 'RAPI ALERTS ' . strtoupper(config('app.env')));
        }

        if (config('app.debug') === true) {
            Config::set('mail.from.name', 'RAPI ALERTS ' . strtoupper(config('app.env')));
        }
        try {
            retry(
                5,
                function () use ($infoEndpoint, $nameException) {
                    $users = explode(',', config('constants.alert_mails'));

                    $data               = [];
                    $data[ 'ip' ]       = $infoEndpoint['from'];
                    $data[ 'endpoint' ] = $infoEndpoint;
                    $data[ 'type' ]     = $nameException;
                    $data[ 'alert' ]    = '';

                    Notification::route('mail', $users)
                        ->notify(
                            new ExceptionEmailNotification
                            (
                                [ 'mail' ], $data
                            )
                        );

                },
                100
            );
        }
        catch(Exception $exception) {
            $request = request();
            $infoEndpoint = GetInfoFromExceptionService::execute($request, $exception);
            $sendLogConsoleService = new SendLogConsoleService();
            $sendLogConsoleService->execute(
                $request,
                'errors',
                'errors',
                'error:' . $exception->getMessage(),
                $infoEndpoint
            );
        }

        return false;
    }

}
