<?php

namespace App\Core\Countries\Services;


use App\Core\Countries\Models\CountryRegionPivot;
use App\Core\Countries\Models\RegionRapi;

class DeleteCountriesOfRegionService
{

    public function execute(RegionRapi $regionRapi)
    {
        CountryRegionPivot::where('id_region', $regionRapi->id_region)->delete();
    }
}
