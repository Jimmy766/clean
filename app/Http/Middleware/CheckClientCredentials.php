<?php

namespace App\Http\Middleware;

use App\Core\Clients\Models\Client;
use App\Core\Clients\Models\ClientProductCountryBlacklist;
use App\Core\Carts\Services\CartUtil;
use App\Core\Rapi\Services\Log;
use App\Core\Base\Services\ClientService;
use App\Core\Base\Services\GetAllValuesFromHeaderService;
use App\Core\Users\Services\GetCountriesByUserIpService;
use App\Core\Base\Services\GetOriginRequestService;
use App\Core\Base\Services\SendLogConsoleService;
use App\Core\Base\Traits\EndpointCache;
use App\Core\Base\Traits\LogCache;
use App\Core\Users\Models\User;
use Closure;
use Illuminate\Support\Facades\Config;
use DB;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\UploadedFileFactory;
use League\OAuth2\Server\ResourceServer;
use Illuminate\Auth\AuthenticationException;
use Laravel\Passport\Exceptions\MissingScopeException;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

//use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
//use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;

class CheckClientCredentials
{
    use LogCache;
    use EndpointCache;
    /**
     * The Resource Server instance.
     *
     * @var \League\OAuth2\Server\ResourceServer
     */
    protected $server;
    /**
     * @var SendLogConsoleService
     */
    private $sendLogConsoleService;
    /**
     * @var GetCountriesByUserIpService
     */
    private $getCountriesByUserIpService;

    /**
     * Create a new middleware instance.
     *
     * @param  \League\OAuth2\Server\ResourceServer $server
     * @return void
     */
    public function __construct(
        ResourceServer $server,
        SendLogConsoleService $sendLogConsoleService,
        GetCountriesByUserIpService $getCountriesByUserIpService
    ) {
        $this->server                      = $server;
        $this->sendLogConsoleService        = $sendLogConsoleService;
        $this->getCountriesByUserIpService = $getCountriesByUserIpService;
    }

    /**
     * Handle an incoming request.
     *
     * @param $request
     * @param Closure $next
     * @param mixed ...$scopes
     *
     * @return mixed
     * @throws AuthenticationException
     * @throws MissingScopeException
     */
    public function handle($request, Closure $next, ...$scopes) {
        $t1 = round(microtime(true) * 1000);
        $token = substr($request->server->get('HTTP_AUTHORIZATION'),7);
        $psr = (new PsrHttpFactory(
            new ServerRequestFactory,
            new StreamFactory,
            new UploadedFileFactory,
            new ResponseFactory
        ))->createRequest($request);
        try {
//            $psr = $this->rememberCache('psr_' . $token, Config::get('constants.cache_5'), function () use ($psr) {
//                return $this->server->validateAuthenticatedRequest($psr);
//            });
            $psr = $this->server->validateAuthenticatedRequest($psr);
        } catch (\Exception $e) {
            $this->sendLogConsoleService->execute($request, 'error', 'access', 'LOGIN ERROR: CheckClientCredentials. request: ' . $request);
            $this->sendLogConsoleService->execute($request, 'error', 'time', 'REQUEST_CC: ' . json_encode($request));
            throw new AuthenticationException;
        }
        $this->validateScopes($psr, $scopes);
        $token = $psr->getAttribute('oauth_access_token_id');
        $request['oauth_client_id'] = (integer)$psr->getAttribute('oauth_client_id');

        $client = $this->rememberCache('client_' . $request["oauth_client_id"], Config::get('constants.cache_daily'), function () use ($request) {
            return Client::find($request["oauth_client_id"]);
        });

        $client = ClientService::getClient($client);

        $client_site = $this->rememberCache('client_site_' . $request["oauth_client_id"], Config::get('constants.cache_daily'), function () use ($client) {
            $site = $client->site;
            return $site ? $site : null;
        });

        $request['client_site_id'] = $client_site ? $client_site->site_id : 1000;
        $request['client_sys_id'] = $client_site ? $client_site->sys_id : 1;
        $request['client_domain'] = $client_site ? $client_site->site_url_https : 'https://www.wintrillions.com';
        $request['client_partner'] = $client->partner_id;
        if (strpos($request->url(), 'live_feed') === false) {
            $request['user_id'] = $this->rememberCache('user_id_token_' . $token, Config::get('constants.cache_hourly'), function () use ($token) {
                $user_id = DB::table('oauth_access_tokens')->where('id', '=', $token)->first()->user_id;
                return $user_id ? $user_id : 0;
            });
        }
        $approved_langs = collect([
            'en-us',
            'es-la',
            'pt-la',
            'en',
            'es',
            'pt',
        ]);
        $request['client_lang'] = ($request['lang'] && $approved_langs->contains($request['lang'])) ? $request['lang'] : ($client_site ? $client_site->site_lang_code : 'en-us');

        $user = false;
        if ($request['user_id'] != 0) {
            $user = $this->rememberCache('user_' . $request['user_id'], Config::get('constants.cache_hourly'), function () use ($request) {
                return User::find($request['user_id']);
            });
            if ($user) {
                $request['user_country'] =  $user->country_id;
            }
        }
        if (request()->user_ip) {

            $userIp =request()->user_ip;
            [ $iso, $countries, $states, $countryIp2loc ] = $this->getCountriesByUserIpService->execute(
                $userIp
            );

            $isoState = $states === null ? null : $states->state_iso;
            $request['client_country_region_iso'] = $isoState;
            if($iso == null){
                return response()->json(
                    [
                        'error' => [
                            "ip_country" => trans('lang.not_valid_ip_country'),
                            "response" => $countryIp2loc,
                        ],
                        'code'  => 422,
                    ],
                    422
                );
            }

            if ($countries->isEmpty()) {
                $code = 422;
                return response()->json(['error' => [
                    "user_ip" => trans('lang.not_valid_ip_country')
                ], 'code' => $code], $code);
            }

            if($user && ClientService::isOrca()){
                $user_country = $user->country;
                $request['client_site_id'] = $user->site_id;
                $request['client_sys_id'] = $user->sys_id;
                $request['client_country_id'] = $user_country->country_id;
                $request['country_currency'] = $user->curr_code;
                $request['client_country_iso'] = $user_country->country_Iso;
                $request['client_country_region'] = $user_country->country_region;

            }else{
                $request['client_country_id'] = $countries->first()->country_id;
                $request['country_currency'] = $countries->first()->country_info ? $countries->first()->country_info->country_currency : 'USD';
                $request['client_country_iso'] = $iso;
                $request['client_country_region'] = $countries->first()->country_region;
            }

            $blocked_country = ClientProductCountryBlacklist::query()
                ->where('clients_products_id', '=', 0)
                ->where('product_type_id', '=', 0)
                ->where('country_id', '=', $request[ 'client_country_id' ])
                ->getFromCache();

            $origin = GetOriginRequestService::execute();
            $activeExceptionDomain = env('DOMAIN_STATIC_EXCEPTION',null) === $origin;

            if ($blocked_country->isNotEmpty() && $activeExceptionDomain === false) {
                $code   = 403;
                $errors = [
                    "blocked_country" => [ trans('lang.blocked_country') ],
                ];
                $data   = [
                    "ip2location" => $countryIp2loc,
                    "ip" => $request->user_ip,
                    'error' => $errors,
                    'code'  => $code,
                ];
                return response()->json(
                    $data,
                    $code
                );
            }
        }

        $this->sendLogConsoleService->execute(
            $request,
            'access',
            'access',
            'DEBUG: User_ip: ' . $request->user_ip . ' - Country_currency: ' . $request[ 'country_currency' ] . ' - country_iso: ' . $request[ 'client_country_iso' ]
        );

        $return = $this->cache($request, $next);


        /**
         * Por problemas con el cart, Si al final del request no se desbloquea el cart, entonces lo desbloqueamos
         */
        if(isset($request->crt_id) && CartUtil::unlock_cart($request->crt_id)){
            $this->sendLogConsoleService->execute(
                $request,
                'error',
                'access',
                'RESPONSE: ' . Log::stringify(json_decode($return->getContent(), true))
            );
            $this->sendLogConsoleService->execute(
                $request,
                'error',
                'access',
                'CART LOCKED AND UNLOCKED request: ' . $request
            );
        }

        return $return;
    }

    /**
     * Validate the scopes on the incoming request.
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $psr
     * @param  array $scopes
     * @return void
     * @throws \Laravel\Passport\Exceptions\MissingScopeException
     */
    protected function validateScopes($psr, $scopes) {
        if (in_array('*', $tokenScopes = $psr->getAttribute('oauth_scopes'))) {
            return;
        }

        foreach ($scopes as $scope) {
            if (!in_array($scope, $tokenScopes)) {
                throw new MissingScopeException($scope);
            }
        }
    }
}
