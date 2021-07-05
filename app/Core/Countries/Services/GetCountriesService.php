<?php

namespace App\Core\Countries\Services;

use App\Core\Clients\Models\ClientProductCountryBlacklist;
use App\Core\Countries\Models\Country;

class GetCountriesService
{

    public function execute()
    {
        $blocked = ClientProductCountryBlacklist::query()
            ->where('clients_products_id', '=', 0)
            ->where('product_type_id', '=', 0)
            ->getFromCache()
            ->pluck('country_id');

        return Country::query()
            ->whereNotIn('country_id', $blocked)
            ->getFromCache();
    }

}
