<?php

namespace App\Http\Middleware;

use App\Core\Clients\Models\Client;
use App\Core\Clients\Models\ClientProductCountryBlacklist;
use App\Core\Clients\Models\ClientProductIpWhitelist;
use App\Core\Countries\Models\Country;
use App\Core\Carts\Services\CartUtil;
use App\Core\Clients\Services\IP2LocTrillonario;
use App\Core\Base\Services\ClientService;
use App\Core\Base\Services\GetAllValuesFromHeaderService;
use App\Core\Base\Services\GetOriginRequestService;
use App\Core\Base\Services\SendLogConsoleService;
use App\Core\Rapi\Models\Site;
use App\Core\Base\Traits\EndpointCache;
use App\Core\Base\Traits\LogCache;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Ip2location\IP2LocationLaravel\IP2LocationLaravel;
use Illuminate\Support\Facades\Cache;

class Authenticate
{
    use LogCache;
    use EndpointCache;
    /**
     * The authentication factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;
    /**
     * @var \App\Core\Base\Services\SendLogConsoleService
     */
    private $sendLogConsoleService;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth, SendLogConsoleService $sendLogConsoleService)
    {
        $this->auth = $auth;
        $this->sendLogConsoleService = $sendLogConsoleService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards)
    {

        $t1 = round(microtime(true) * 1000, 2);
        $this->authenticate($guards);

        try {
            $request['oauth_client_id'] = $request->user()->token()->client_id;
        } catch (\Exception $exception) {
            $this->sendLogConsoleService->execute($request, 'errors', 'errors', 'AUTH_ERROR: AccessingToken: ' . $exception->getMessage());
            $this->sendLogConsoleService->execute($request, 'request-response-time', 'time', 'REQUEST_AUTH: ' . json_encode($request));
        }

        $client = $this->rememberCache('client_'.$request["oauth_client_id"], Config::get('constants.cache_daily'), function () use ($request) {
            return Client::find($request["oauth_client_id"]);
        });

        $client = ClientService::getClient($client);


        $client_site = $this->rememberCache('client_site_'.$request["oauth_client_id"], Config::get('constants.cache_daily'), function () use ($client) {
            $site = $client->site;
            return $site ? $site : null;
        });

        $request['client_site_id'] = $client_site ? $client_site->site_id : 1000;
        $request['client_sys_id'] = $client_site ? $client_site->sys_id : 1;
        $request['user_id'] = $this->auth->user()->usr_id;
        $request['client_domain'] = $client_site ? $client_site->site_url_https : 'https://www.wintrillions.com';
        $request['client_partner'] = $client->partner_id;
        $approved_langs = collect([
            'en-us',
            'es-la',
            'pt-la',
            'en',
            'es',
            'pt',
        ]);
        $request['client_lang'] = ($request['lang'] && $approved_langs->contains($request['lang'])) ? $request['lang'] : ($client_site ? $client_site->site_lang_code : 'en-us');

        if($request->user_ip) {

            $ip_whitelist = ClientProductIpWhitelist::query()->where('ip', '=', request()->user_ip)
                ->firstFromCache();
            $country_from_ip = ($ip_whitelist != "" && $ip_whitelist != null) ? $ip_whitelist->country_iso_to_use : "";

            [$iso, $state, $country] = IP2LocTrillonario::get_iso($country_from_ip);

            if($iso == null){
                return response()->json(
                    [
                        'error' => [
                            "ip_country" => trans('lang.not_valid_ip_country'),
                            "response" => $country,
                        ],
                        'code'  => 422,
                    ],
                    422
                );
            }


            $country = $this->rememberCache('country_'.$iso,
                Config::get('constants.cache_daily'), function() use ($iso) {
                    return Country::with('country_info')->where('country_Iso', $iso)->get();
                });

            if ($country->isEmpty()) {
                $this->sendLogConsoleService->execute($request, 'error', 'access', 'AUTH_ERROR: Authenticate - EmptyCountry. request: ' . $request);
                $this->sendLogConsoleService->execute(
                    $request,
                    'error',
                    'time',
                    'REQUEST_AUTH: ' . json_encode($request)
                );
                $code = 422;
                return response()->json(['error' => [
                    "ip_country" => trans('lang.not_valid_ip_country')
                ], 'code' => $code], $code);
            }

            $request['client_country_id'] = $country->first()->country_id;
            $request['country_currency'] = $country->first()->country_info ? $country->first()->country_info->country_currency : 'USD';
            $request['client_country_iso'] = $iso;

            $blocked_country = ClientProductCountryBlacklist::query()
                ->where('clients_products_id', '=', 0)
                ->where('product_type_id', '=', 0)
                ->where('country_id', '=', $request[ 'client_country_id' ])
                ->getFromCache();
            $origin = GetOriginRequestService::execute();
            $activeExceptionDomain = env('DOMAIN_STATIC_EXCEPTION',null) === $origin;

            if ($blocked_country->isNotEmpty() && $activeExceptionDomain === false) {
                $this->sendLogConsoleService->execute($request, 'error', 'access', 'AUTH_ERROR: Authenticate - BlockedCountry. request: ' . $request);
                $this->sendLogConsoleService->execute(
                    $request,
                    'error',
                    'time',
                    'REQUEST_AUTH: ' . json_encode($request)
                );
                $code = 403;
                return response()->json(['error' => [
                    "blocked_country" => [ trans('lang.blocked_country') ] ],
                    'code' => $code], $code);
            }
        }

        $return = $this->cache($request, $next);

        return $return;

    }

    /**
     * Determine if the user is logged in to any of the given guards.
     *
     * @param  array  $guards
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function authenticate(array $guards)
    {
        if (empty($guards)) {

            return $this->auth->authenticate();
        }

        foreach ($guards as $guard) {
            if ($this->auth->guard($guard)->check()) {
                return $this->auth->shouldUse($guard);
            }
        }

        throw new AuthenticationException('Unauthenticated.', $guards);
    }
}
