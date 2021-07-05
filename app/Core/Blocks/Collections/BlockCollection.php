<?php

namespace App\Core\Blocks\Collections;

use App\Core\Base\Collections\CoreResourceCollection;
use App\Core\Blocks\Resources\BlockResource;

class BlockCollection extends CoreResourceCollection
{
    public $collects = BlockResource::class;
}
