<?php

namespace App\Core\Casino\Collections;

use App\Core\Base\Collections\CoreResourceCollection;
use App\Core\Casino\Resources\CasinoCategoryResource;
use App\Core\Casino\Resources\CasinoGameResource;

class CasinoCategoryCollection extends CoreResourceCollection
{

    public $collects = CasinoCategoryResource::class;
}
