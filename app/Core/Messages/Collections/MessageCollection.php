<?php

namespace App\Core\Messages\Collections;

use App\Core\Base\Collections\CoreResourceCollection;
use App\Core\Messages\Resources\MessageResource;

class MessageCollection extends CoreResourceCollection
{

    public $collects = MessageResource::class;
}
