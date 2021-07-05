<?php

namespace App\Core\Assets\Collections;

use App\Core\Base\Collections\CoreResourceCollection;
use App\Core\Assets\Resources\AssetResource;

class AssetCollection extends CoreResourceCollection
{

    public $collects = AssetResource::class;
}
