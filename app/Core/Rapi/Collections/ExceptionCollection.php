<?php

namespace App\Core\Rapi\Collections;

use App\Core\Base\Collections\CoreResourceCollection;
use App\Core\Rapi\Resources\ExceptionResource;

class ExceptionCollection extends CoreResourceCollection
{
    public $collects = ExceptionResource::class;
}
