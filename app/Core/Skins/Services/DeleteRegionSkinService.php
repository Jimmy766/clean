<?php

namespace App\Core\Skins\Services;

use App\Core\Skins\Models\RegionSkin;
use App\Core\Skins\Models\Skin;

class DeleteRegionSkinService
{

    public function execute(Skin $skin)
    {
        RegionSkin::where('id_skin', $skin->id_skin)->delete();
    }
}
