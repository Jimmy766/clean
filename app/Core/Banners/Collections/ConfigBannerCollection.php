<?php

namespace App\Core\Banners\Collections;

use App\Core\Banners\Resources\ConfigBannerResource;
use App\Core\Base\Collections\CoreResourceCollection;

class ConfigBannerCollection extends CoreResourceCollection
{
    public $collects = ConfigBannerResource::class;
}
