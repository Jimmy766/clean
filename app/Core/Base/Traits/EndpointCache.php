<?php

namespace App\Core\Base\Traits;

use App\Core\Base\Services\SendLogConsoleService;
use Illuminate\Support\Facades\Cache;

trait EndpointCache
{
    public function cache($request, \Closure $next) {
        $exclude_endpoints = [
            '/api/cashier',
            '/api/users/wallet',
            '/api/users/logout',
            '/api/users/alerts/lotteries',
            '/api/users/details',
            '/api/users/last_cart',
            '/api/user_transactions',
        ];
        $exclude_endpoints_parameters = [
            '/api/carts/',
            '/api/cart_lotteries/',
            '/api/cart_syndicates/',
            '/api/cart_scratch_cards/',
            '/api/cart_live_lottery/',
            '/api/cart_raffles/',
            '/api/cart_raffle_syndicates/',
            '/api/cart_memberships/',
            '/api/reports',
            '/api/scratch_card_subscriptions',
            '/api/lottery_syndicate_subscriptions',
            '/api/lotteries_subscriptions',
            '/api/live_lottery_subscriptions',
            '/api/syndicate_raffle_subscriptions',
            '/api/raffle_subscriptions',
            '/api/telem/prices'
        ];
        $url = $request->server('REDIRECT_URL');
        if ($request->server('REQUEST_METHOD') === 'GET' && !in_array($url, $exclude_endpoints) && !config("app.env") == "local") {
            foreach ($exclude_endpoints_parameters as $x) {
                if (strpos($url, $x) !==  false) {
                    return $next($request);
                }
            }
            $url = $request->url();
            $sendLogConsoleService = new SendLogConsoleService();
            $tag = 'PARAMS: '.json_encode($request);
            $sendLogConsoleService->execute($request, 'access', 'access', $tag);

            $queryParamsCache = [
                'client_site_id' => $request['client_site_id'],
                'client_sys_id' => $request['client_sys_id'],
                'client_lang' => $request['client_lang'],
                'client_domain' => $request['client_domain'],
                'client_partner' => $request['client_partner'],
                'client_country_id' => $request['client_country_id'],
                'country_currency' => $request['country_currency'],
            ];
            ksort($queryParamsCache);
            $queryString = implode('____', $queryParamsCache);
            $fullUrl = "{$url}___{$queryString}";
            $sendLogConsoleService = new SendLogConsoleService();
            $sendLogConsoleService->execute($request, 'access', 'access', $fullUrl);
            return Cache::remember($fullUrl, 3, function () use ($next, $request) {
                return $next($request);
            });
        } else {
            return $next($request);
        }
   }
}
