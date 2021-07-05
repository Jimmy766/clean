<?php

namespace App\Core\Messages\Collections;

use App\Core\Messages\Resources\MessageTemplateResource;
use App\Core\Base\Collections\CoreResourceCollection;

class MessageTemplateCollection extends CoreResourceCollection
{
    public $collects = MessageTemplateResource::class;
}
