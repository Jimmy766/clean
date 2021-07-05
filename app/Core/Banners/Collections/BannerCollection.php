<?php

namespace App\Core\Banners\Collections;

use App\Core\Base\Collections\CoreResourceCollection;
use App\Core\Banners\Resources\BannerResource;

class BannerCollection extends CoreResourceCollection
{
    public $collects = BannerResource::class;
}
