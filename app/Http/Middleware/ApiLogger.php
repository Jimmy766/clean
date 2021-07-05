<?php

namespace App\Http\Middleware;

use App\Core\Rapi\Services\DBLog;
use App\Core\Base\Services\SendLogConsoleService;
use App\Core\Base\Traits\LogCache;
use Closure;
use Illuminate\Support\Str;

class ApiLogger
{
    /**
     * @var SendLogConsoleService
     */
    private $sendLogConsoleService;

    public function __construct(
        SendLogConsoleService $sendLogConsoleService
    ) {
        $this->sendLogConsoleService = $sendLogConsoleService;
    }

    use LogCache;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        /* Do not log */
        if(str_contains($request->getRequestUri(),"/api/documentation") ){
            return $next($request);
        }



        $infoGeneralRequest = '';
        $token = md5(uniqid(rand(), true));
        $request['tk'] = $token;
        $t0 = round(microtime(true) * 1000);
        $this->sendLogConsoleService->execute($request, 'request-response-time', 'access', ' ACCESSLOG_START, TOKENLOGGER --- TIME: ' . round($t0 - LARAVEL_START * 1000, 2) . ' - Request: ' . $infoGeneralRequest);
        $t1 = round(microtime(true) * 1000);
        $return = $next($request);

        $this->sendLogConsoleService->execute($request, 'request-response-time', 'access', ' ACCESSLOG_LOAD: TIME: ' . round($t1 - LARAVEL_START * 1000, 2) . ' Request: ' . $infoGeneralRequest);

        $this->sendLogConsoleService->execute($request, 'request-response-time', 'access', ' ACCESSLOG_PROCESS: TIME: ' . ( round(( microtime(true) * 1000 ) - $t1, 2) ) . ' Request: ' . $infoGeneralRequest);

        $accesslog_fulltime = round((microtime(true) * 1000) - LARAVEL_START * 1000, 2);
        $this->sendLogConsoleService->execute($request, 'request-response-time', 'access', ' ACCESSLOG_FULL: TIME: ' . $accesslog_fulltime . ' Request: ' . $infoGeneralRequest);

        if ($accesslog_fulltime > 15000) {
            // tiempo mayor a 15 seg, revisar demoras
            $this->sendLogConsoleService->execute($request, 'request-response-time', 'access', 'ACCESSLOG_LONGTIME: TIME: ' . $accesslog_fulltime . ' Request: ' . $infoGeneralRequest);
        }

        if (str_contains($request->getRequestUri(),"/oauth/token") ) {
            $this->sendLogConsoleService->execute($request, 'request-response-time', 'access', 'LOGIN_RESULT: ' . $return);
        }

        $total_query_time = DBLog::getInstance()->getTime();

        $this->sendLogConsoleService->execute($request, 'query-duplicate-time', 'query', ( $total_query_time <
            5000
                ? 'MYSQL_LOG_TOTALTIME: '
                : 'MYSQL_LOG_ALERT_TOTALTIME: ' ) . $total_query_time . ' Request: ' . $infoGeneralRequest);

        $total_query = DBLog::getInstance()->getTotalQueries();

        $this->sendLogConsoleService->execute($request, 'query-duplicate-time', 'query', ( $total_query < 200
                ? 'MYSQL_LOG_TOTALQUERIES: '
                : 'MYSQL_LOG_TOTAL_ALERT_QUERIES: ' ) . $total_query . ' Request: ' . $infoGeneralRequest);

        foreach(DBLog::getInstance()->getDuplicateQueries() as $qq => $times){
            $array = [];
            $array['info_general'] = "";
            $array['query_details'] = DBLog::getInstance()->getQueryDetail($qq);
            $tag = ( $times <= 15
                    ? 'MYSQL_LOG_DUPLICATE: '
                    : 'MYSQL_LOG_ALERT_DUPLICATE: ' );
            $searches = ['oauth_clients', 'oauth_access_tokens'];
            if (Str::contains($qq, $searches) === false) {
                $this->sendLogConsoleService->execute(
                    $request,
                    'query-duplicate-time',
                    'query',
                    $tag,
                    $array
                );
            }
        }

        foreach(DBLog::getInstance()->getLongQueries() as $qq => $time){
            $array = [];
            $array['info_general'] = "";
            $array['query_details'] = DBLog::getInstance()
                ->getQueryDetail($qq);
            $tag = 'MYSQL_LOG_LONG_QUERY: ';
            $searches = ['xmlregister'];
            $path = $request->path();
            if (Str::contains($path, $searches) === false) {
                $this->sendLogConsoleService->execute(
                    $request,
                    'query-duplicate-time',
                    'query',
                    $tag,
                    $array
                );
            }
        }

        return $return;
    }


    public function terminate($request, $response)
    {
        $endTime = microtime(true);
        $dataToLog = [];
        $dataToLog[ 'time' ] = gmdate("F j, Y, g:i a");
        $dataToLog[ 'duration' ] = number_format($endTime - LARAVEL_START, 3);
        $dataToLog[ 'response' ] = $response;

        $this->sendLogConsoleService->execute(
            $request,
            'request-response',
            'access',
            "REQUEST AND RESPONSE",
            $dataToLog
        );
    }
}
