<?php

namespace App\Core\Skins\Services;

use App\Core\Skins\Models\ConfigSkin;
use App\Core\Skins\Models\FileSkin;
use App\Core\Skins\Models\Skin;
use App\Core\Skins\Models\TextSkin;

class DeleteConfigSkinService
{

    public function execute(Skin $skin)
    {
        $configSkins = ConfigSkin::where('id_skin', $skin->id_skin)->get( [ 'id_config_skin' ] );

        $arrayIdConfigSkin = $configSkins->pluck('id_config_skin')->toArray();
        FileSkin::whereIn('id_config_skin', $arrayIdConfigSkin)->delete();
        TextSkin::whereIn('id_config_skin', $arrayIdConfigSkin)->delete();
        ConfigSkin::where('id_skin', $skin->id_skin)->delete();
    }
}
