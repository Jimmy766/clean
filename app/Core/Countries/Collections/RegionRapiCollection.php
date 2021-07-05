<?php

namespace App\Core\Countries\Collections;

use App\Core\Base\Collections\CoreResourceCollection;
use App\Core\Countries\Resources\RegionRapiResource;

class RegionRapiCollection extends CoreResourceCollection
{

    public $collects = RegionRapiResource::class;
}
