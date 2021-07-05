<?php

namespace App\Core\Messages\Collections;

use App\Core\Messages\Resources\MessageTemplateTypeResource;
use App\Core\Base\Collections\CoreResourceCollection;

class MessageTemplateTypeCollection extends CoreResourceCollection
{
    public $collects = MessageTemplateTypeResource::class;
}
