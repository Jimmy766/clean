<?php

namespace App\Core\Slides\Services;

use App\Core\Slides\Models\ImageSlide;
use App\Core\Slides\Models\Slide;

/**
 * Class DeleteImageSlideService
 * @package App\Services
 */
class DeleteImageSlideService
{

    public function execute(Slide $slide)
    {
        ImageSlide::where('id_slide', $slide->id_slide)->delete();
    }
}
