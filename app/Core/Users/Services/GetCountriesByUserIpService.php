<?php

namespace App\Core\Users\Services;

use App\Core\Clients\Models\ClientProductIpWhitelist;
use App\Core\Countries\Models\Country;
use App\Core\Clients\Services\IP2LocTrillonario;
use App\Core\Rapi\Models\State;

class GetCountriesByUserIpService
{

    public function execute($userIp)
    {
        $ipWhitelist = ClientProductIpWhitelist::query()
            ->where('ip', '=', $userIp)
            ->firstFromCache();

        $countryFromIp = ( $ipWhitelist != "" && $ipWhitelist != null )
            ? $ipWhitelist->country_iso_to_use : "";

        [$iso, $region, $countryIp2loc]     = IP2LocTrillonario::get_iso($countryFromIp);
        $states = State::query()->where('state_name', $region)->firstFromCache();

        $country = Country::query()
            ->with('country_info')
            ->where('country_Iso', $iso)
            ->getFromCache();
        return [ $iso, $country, $states, $countryIp2loc, $ipWhitelist, $countryFromIp ];
    }

}
