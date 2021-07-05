<?php

namespace App\Core\Base\Services;

use App\Core\Base\Traits\LogCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SendLogConsoleService
{

    use LogCache;

    public function execute(Request $request, $keyLog, $type, $tag, $array = [])
    {
        if(!is_array($array)){
            $array = [];
        }
        $documentation = str_contains($request->getRequestUri(), "/api/documentation");
        $docsAsset     = str_contains($request->getRequestUri(), "/docs/asset");
        $apiDocs       = str_contains($request->getRequestUri(), "/docs/api-docs.json");
        if ($documentation || $docsAsset || $apiDocs) {
            return null;
        }

        $this->logConsole($request, $keyLog, $type, $tag, $array);
    }

    private function logConsole(Request $request, $keyLog, $type, $tag, $array = []): void
    {
        $array = $this->getBasicInfoLog($request, $keyLog, $array);
        $this->sendToConsole( $keyLog, $type, $tag, $array);
    }

    private function getBasicInfoLog(Request $request, $keyLog, $array)
    {
        $parameters = $request->request->all();
        if (isset($parameters[ 'password' ])) {
            unset($parameters[ 'password' ]);
        }
        $headers = GetAllValuesFromHeaderService::execute($request);
        $headers = $headers->toArray();
        $origin = array_key_exists('origin', $headers) ? $headers['origin'] : null;
        $idUser                           = Auth::id();
        $arrayLocal[ 'from' ]             = $origin;
        $arrayLocal[ 'url' ]              = $request->getRequestUri();
        $arrayLocal[ 'ip' ]               = $request->ip();
        $arrayLocal[ 'method' ]           = $request->method();
        $arrayLocal[ 'id_user' ]          = $idUser;
        $arrayLocal[ 'request_language' ] = app()->getLocale();
        $arrayLocal[ 'set_key_inf_user' ] = "info-{$idUser}-{$keyLog}";
        $arrayLocal[ 'time' ]             = gmdate("F j, Y, g:i a");
        $arrayLocal[ 'request_all' ]      = $parameters;
        $array                            = array_merge($arrayLocal, $array);

        return $array;
    }

    private function sendToConsole($keyLog, $type, $tag, $array = [])
    {
        $data               = [];
        $data[ 'data_log' ] = $array;
        $data[ 'tag' ]      = $tag;
        $data[ 'type' ]     = $type;
        $data[ 'key_log' ]  = $keyLog;
        $env             = config('app.env');
        $strUpper         = strtoupper($type);
        $requestTk = request("tk");
        $data[ 'extra_info' ] = "{{$env}} [{$strUpper}]: {$requestTk} --- {$type}";

        $dataCollection = collect([]);
        $dataCollection->push($data);

        $logEnv = env('LOG_DISABLE_REQUEST_AND_RESPONSE', null);
        $arrayNotPermitLog = [
            'request-response',
            'request-response-time',
            'query-duplicate-time',
        ];
        if (empty($logEnv) === false && in_array($keyLog, $arrayNotPermitLog) === true) {
            return null;
        }
        $this->recordLog($type, "", $data);
    }


    private function mapChangeToStringTransform(): callable
    {
        return static function ($item, $key) {
            $array = "";
            $string   = "";
            if (is_array($item)) {
                $checkCount = $item[ 'array' ];
                if(is_array($checkCount)){
                    if (count($checkCount) > 0) {
                        $array = json_encode($item[ 'array' ]);
                    }
                }
                $jumpLine = "                                                        ";
                $string   = $item[ 'tag' ] . " " . $array . " " . $jumpLine;
            }
            return [ 'string' => $string ];
        };
    }

}
