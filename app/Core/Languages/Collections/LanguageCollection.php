<?php

namespace App\Core\Languages\Collections;

use App\Core\Base\Collections\CoreResourceCollection;
use App\Core\Languages\Resources\LanguageResource;

class LanguageCollection extends CoreResourceCollection
{

    public $collects = LanguageResource::class;
}
