<?php

namespace App\Core\Slides\Services;

use App\Core\Countries\Models\CountryRegionPivot;
use App\Core\Countries\Models\RegionRapi;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StoreCountriesOfRegionService
{

    public function execute(RegionRapi $regionRapi, Request $request)
    {
        $countries = $request->input('countries');
        $countries = collect($countries);

        $countries = $countries->map($this->mapSetSlideToConfigTransform($regionRapi));

        CountryRegionPivot::insert($countries->toArray());
    }

    private function mapSetSlideToConfigTransform(RegionRapi $regionRapi): callable
    {
        return static function ($item, $key) use ($regionRapi) {

            $item['id_region'] = $regionRapi->id_region;
            $item['created_at'] = Carbon::now();

            return $item;
        };
    }

}
