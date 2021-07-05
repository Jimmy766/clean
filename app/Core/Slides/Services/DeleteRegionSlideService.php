<?php

namespace App\Core\Slides\Services;

use App\Core\Slides\Models\RegionSlidePivot;
use App\Core\Slides\Models\Slide;

class DeleteRegionSlideService
{

    public function execute(Slide $slide)
    {
        RegionSlidePivot::where('id_slide', $slide->id_slide)->delete();
    }
}
