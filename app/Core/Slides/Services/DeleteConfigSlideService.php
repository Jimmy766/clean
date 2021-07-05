<?php

namespace App\Core\Slides\Services;

use App\Core\Slides\Models\ConfigSlide;
use App\Core\Slides\Models\Slide;

class DeleteConfigSlideService
{

    public function execute(Slide $slide)
    {
        ConfigSlide::where('id_slide', $slide->id_slide)->delete();
    }
}
