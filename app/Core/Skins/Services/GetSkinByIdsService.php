<?php

namespace App\Core\Skins\Services;

use App\Core\Base\Classes\ModelConst;
use App\Core\Countries\Models\RegionRapi;
use App\Core\Skins\Models\Skin;
use App\Core\Terms\Models\Language;

class GetSkinByIdsService
{

    public function execute($idsSkin)
    {
        $tag = [ Skin::TAG_CACHE_MODEL, RegionRapi::TAG_CACHE_MODEL, Language::TAG_CACHE_MODEL ];
        $relations = [
            'programSkin.datePrograms',
            'configSkin.files',
            'configSkin.texts',
            'configSkin.languages',
        ];
        return Skin::query()
            ->whereIn('id_skin', $idsSkin->toArray())
            ->with($relations)
            ->where('status', ModelConst::ENABLED)
            ->where('active', ModelConst::ENABLED)
            ->getFromCache([ '*' ], $tag);
    }
}
