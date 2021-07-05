<?php

namespace App\Core\Banners\Services;

use App\Core\Banners\Models\Banner;
use App\Core\Banners\Models\ConfigBanner;
use Illuminate\Http\Request;

class StoreConfigBannerService
{


    public function execute(Banner $banner, Request $request)
    {
        $config = $request->config;
        $config = collect($config);

        $configs = $config->map($this->mapSetBannerToConfigTransform($banner));
        ConfigBanner::insert($configs->toArray());
    }

    private function mapSetBannerToConfigTransform(Banner $banner): callable
    {
        return static function ($item, $key) use ($banner) {

            $newItem = [];
            $newItem['title'] = $item['title'];
            $newItem['subtitle'] = $item['subtitle'];
            $newItem['id_banner'] = $banner->id_banner;
            $newItem['id_language'] = $item['id_language'];

            return $newItem;
        };
    }

}
