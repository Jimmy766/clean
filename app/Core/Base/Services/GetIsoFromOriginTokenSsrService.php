<?php

namespace App\Core\Base\Services;

use App\Core\Base\Classes\ModelConst;
use App\Core\Clients\Models\CredentialIso;
use App\Core\Countries\Models\Country;
use Illuminate\Support\Facades\Cache;

/**
 * Class GetIsoFromOriginService
 * @package App\Services
 */
class GetIsoFromOriginTokenSsrService
{

    /**
     * @return mixed|null
     */
    public static function execute()
    {
        $tokenRequest = request()->header('set-tkssr');
        $typeClient   = request()->header('tk-client');

        $key = ModelConst::KEY_CACHE_TOKEN_SSR;
        if ($typeClient !== null) {
            $key .= "-" . $typeClient;
        }
        $tokenCache = Cache::get($key);

        $origin = GetOriginRequestService::execute();

        if ($tokenCache === null) {
            return [ null, null ];
        }

        if ($origin === null) {
            return [ null, null ];
        }

        if ( $tokenRequest !== $tokenCache ) {
            return [ null, null ];
        }

        $country   = null;
        $clientIso = CredentialIso::query()
            ->join( 'oauth_clients as ac', 'credentials_iso.id_credential', '=', 'ac.id' )
            ->where( 'ac.name', '=', $origin )
            ->where( 'credentials_iso.iso', '!=', '' )
            ->firstFromCache( [ 'credentials_iso.iso' ] )
        ;

        if ( $clientIso === null ) {
            return [ null, null ];
        }

        $columns = [ 'country_name_en as country', 'country_Iso as region', 'country_Iso as code' ];
        $country = Country::query()->where( 'country_Iso', $clientIso->iso )->firstFromCache( $columns );

        if ( $country === null ) {
            return [ null, null ];
        }

        return [ $clientIso->iso, $country->toArray() ];
    }

}
