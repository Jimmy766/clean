<?php

namespace App\Http\Middleware;

use App\Core\Base\Services\SendLogConsoleService;
use Closure;
use GuzzleHttp\Client as ClientHttp;

class PerformanceCheck
{

    public function __construct()
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    public function terminate($request, $response)
    {
        //get elastic timezone
        $elasticTz = config('elastic.elastic_timezone');
        //messure the time and convert to ms
        $executionTime = round(( microtime(true) - LARAVEL_START ) * 1000);
        //create DateTime Object
        $dateTime = new \DateTime();
        //transform to elastic date format
        $dateTime = str_replace(" ", "T", $dateTime->format('Y-m-d H:i:s'));
        //Add timezone
        $dateTime .= $elasticTz;
        //get method and endpoint from request
        $endpoint        = $request->method() . ' ' . $request->path();
        $endpointGeneral = "";

        $fragments = explode("/", $endpoint);
        if (is_numeric(end($fragments))) {
            $endpointGeneral = str_replace(end($fragments), "", $endpoint);
        } else {
            $endpointGeneral = $endpoint;
        }

        $payload = [
            'date'             => $dateTime,
            'response_time'    => $executionTime,
            'endpoint'         => $endpoint,
            'endpoint_generic' => $endpointGeneral,
        ];

        $sendLogConsoleService = new SendLogConsoleService();
        $sendLogConsoleService->execute($request, 'access', 'access', 'terminate', $payload);
    }
}
