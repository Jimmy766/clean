<?php

namespace App\Core\Banners\Services;

use App\Core\Banners\Models\Banner;
use App\Core\Banners\Models\RegionBannerPivot;

class DeleteRegionBannerService
{

    public function execute(Banner $banner)
    {
        RegionBannerPivot::where('id_banner', $banner->id_banner)->delete();
    }
}
