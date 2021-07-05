<?php

namespace App\Core\Banners\Services;

use App\Core\Banners\Models\Banner;
use App\Core\Banners\Models\ConfigBanner;

/**
 * Class DeleteConfigBannerService
 * @package App\Services
 */
class DeleteConfigBannerService
{

    public function execute(Banner $banner)
    {
        ConfigBanner::where('id_banner', $banner->id_banner)->delete();
    }
}
