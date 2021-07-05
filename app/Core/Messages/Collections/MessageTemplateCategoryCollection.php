<?php

namespace App\Core\Messages\Collections;

use App\Core\Base\Collections\CoreResourceCollection;
use App\Core\Messages\Resources\MessageTemplateCategoryResource;

class MessageTemplateCategoryCollection extends CoreResourceCollection
{
    public $collects = MessageTemplateCategoryResource::class;
}
