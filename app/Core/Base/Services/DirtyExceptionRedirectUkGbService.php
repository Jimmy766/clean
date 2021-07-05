<?php

namespace App\Core\Base\Services;

use App\Core\Clients\Models\Client;
use App\Core\Countries\Models\CountryRegion;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class DirtyExceptionRedirectUkGbService
{

    /*
     * dirty exception
     * domain: http://www.wintrillions.co.uk/
     * client: 110883
     * country_iso: [uk,gb]
     * */
    public function execute($request): array
    {
        $activeDirtyUkGb     = false;
        $data                = collect([]);
        $isoCountry          = strtolower($request[ 'client_country_iso' ]);
        $countryCheck        = $isoCountry === 'uk' || $isoCountry === 'gb';
        $idClientFromWrapper = $request[ 'oauth_client_id' ];
        $client = Cache::remember(
            'client_' . $request[ "oauth_client_id" ],
            Config::get('constants.cache_daily'),
            function () use ($request) {
                return Client::find($request[ "oauth_client_id" ]);
            }
        );
        $domainClient = $client->site !== null ? $client->site->site_url_https : null;

        $idCountryRegion   = $request[ 'client_country_region' ];
        $countryRegion     = CountryRegion::where('country_region_id', '=', $idCountryRegion)
            ->first();
        $codeCountryRegion = $countryRegion != "" && $countryRegion != null
            ? $countryRegion->country_region_code : "ROW";


        $value = [
            'client_id' => $request['oauth_client_id'],
            'domain' => $domainClient,
            'client_currency' => 'USD',
            'client_lang' => 'en',
            'region' => $codeCountryRegion,
        ];

        // different clientId and same country iso, active redirect
        if ($idClientFromWrapper !== 110883 && $countryCheck === true) {
            $client = Cache::remember(
                'client_110883',
                Config::get('constants.cache_daily'),
                function () use ($request) {
                    return Client::find(110883);
                }
            );
            $domainClient = $client->site !== null ? $client->site->site_url_https : null;
            $value[ 'reason' ] = 'dirty_redirection_uk_or_gb';
            $value[ 'redirect_code' ]        = 302;
            $value[ 'domain' ]        = $domainClient;
            $data                     = collect($value);
            $activeDirtyUkGb          = true;
        }

        // same clientId and different country, off redirect
        if ($idClientFromWrapper === 110883 && $countryCheck === false) {
            $value[ 'reason' ] = 'dirty_ok_domain_uk_or_gb';
            $value[ 'redirect_code' ]        = 200;
            $data                     = collect($value);
            $activeDirtyUkGb          = true;
        }

        // same clientId and same country, off redirect
        if ($idClientFromWrapper === 110883 && $countryCheck === true) {
            $value[ 'reason' ] = 'dirty_ok_domain_uk_or_gb';
            $value[ 'redirect_code' ]        = 200;
            $data                     = collect($value);
            $activeDirtyUkGb          = true;
        }

        return [ $activeDirtyUkGb, $data ];
    }
}
