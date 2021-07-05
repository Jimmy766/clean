<?php

namespace App\Core\Slides\Collections;

use App\Core\Base\Collections\CoreResourceCollection;
use App\Core\Slides\Resources\SlideResource;

class SlideCollection extends CoreResourceCollection
{

    public $collects = SlideResource::class;
}
