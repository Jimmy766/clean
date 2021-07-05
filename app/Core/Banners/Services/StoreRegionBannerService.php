<?php

namespace App\Core\Banners\Services;

use App\Core\Banners\Models\Banner;
use App\Core\Banners\Models\RegionBannerPivot;
use Illuminate\Http\Request;

/**
 * Class StoreRegionBanner
 * @package App\Services
 */
class StoreRegionBannerService
{

    public function execute(Banner $banner, Request $request)
    {
        $regions = $request->input('regions');
        $regions = collect($regions);

        if ($regions->count() > 0) {
            $regions = $regions->map($this->mapSetBannerToRegionTransform($banner));

            RegionBannerPivot::insert($regions->toArray());
        }
    }

    private function mapSetBannerToRegionTransform(Banner $banner): callable
    {
        return static function ($item, $key) use ($banner) {
            $newItem[ 'id_banner' ] = $banner->id_banner;
            $newItem[ 'id_region' ] = $item[ 'id_region' ];

            return $newItem;
        };
    }
}
