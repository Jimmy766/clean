<?php

namespace App\Core\Messages\Collections;

use App\Core\Base\Collections\CoreResourceCollection;
use App\Core\Messages\Resources\MessageTemplateLanguageResource;

class MessageTemplateLanguageCollection extends CoreResourceCollection
{
    public $collects = MessageTemplateLanguageResource::class;
}
