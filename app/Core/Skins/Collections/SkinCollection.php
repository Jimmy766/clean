<?php

namespace App\Core\Skins\Collections;

use App\Core\Base\Collections\CoreResourceCollection;
use App\Core\Skins\Resources\SkinResource;

class SkinCollection extends CoreResourceCollection
{

    public $collects = SkinResource::class;
}
