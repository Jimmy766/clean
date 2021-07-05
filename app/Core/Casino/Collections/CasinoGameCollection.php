<?php

namespace App\Core\Casino\Collections;

use App\Core\Base\Collections\CoreResourceCollection;
use App\Core\Casino\Resources\CasinoGameResource;

class CasinoGameCollection extends CoreResourceCollection
{

    public $collects = CasinoGameResource::class;
}
