<?php

namespace App\Core\SportBooks\Collections;

use App\Core\Base\Collections\CoreResourceCollection;
use App\Core\SportBooks\Resources\SportBookGameResource;

class SportBooksGameCollection extends CoreResourceCollection
{
    public $collects = SportBookGameResource::class;
}
