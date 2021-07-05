<?php

namespace App\Core\Casino\Collections;

use App\Core\Base\Collections\CoreResourceCollection;
use App\Core\Casino\Resources\FavoriteResource;

class FavoriteCollection extends CoreResourceCollection
{

    public $collects = FavoriteResource::class;
}
