<?php

namespace App\Core\Slides\Services;

use App\Core\Slides\Models\DateProgram;
use App\Core\Slides\Models\ProgramSlide;
use App\Core\Slides\Models\Slide;

class DeleteProgramSlideService
{

    public function execute(Slide $slide)
    {
        $programSlide = ProgramSlide::where('id_slide', $slide->id_slide)->get([ 'id_program' ]);

        DateProgram::whereIn('id_program', $programSlide->toArray())->delete();

        ProgramSlide::where('id_slide', $slide->id_slide)->delete();

    }
}
