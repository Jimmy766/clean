<?php

namespace App\Core\Slides\Services;

use App\Core\Base\Classes\ModelConst;
use App\Core\Terms\Models\Language;
use App\Core\Countries\Models\RegionRapi;
use App\Core\Slides\Models\Slide;

/**
 * Class GetSlideByIdsService
 * @package App\Services
 */
class GetSlideByIdsService
{

    public function execute($idsSlide)
    {
        $tag = [ Slide::TAG_CACHE_MODEL, RegionRapi::TAG_CACHE_MODEL, Language::TAG_CACHE_MODEL ];
        $relations = [
            'programSlide.datePrograms',
            'images.asset',
            'configSlide.language',
        ];
        return Slide::query()
            ->whereIn('id_slide', $idsSlide->toArray())
            ->where('status', ModelConst::ENABLED)
            ->where('active', ModelConst::ENABLED)
            ->with($relations)
            ->getFromCache(['*'], $tag);

    }

}
