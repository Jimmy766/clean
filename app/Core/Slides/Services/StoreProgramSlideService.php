<?php

namespace App\Core\Slides\Services;

use App\Core\Base\Classes\ModelConst;
use App\Core\Slides\Models\DateProgram;
use App\Core\Slides\Models\ProgramSlide;
use App\Core\Slides\Models\Slide;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Class StoreProgramSlide
 * @package App\Services
 */
class StoreProgramSlideService
{

    public function execute(Slide $slide, Request $request)
    {
        $typeRangeProgram = $request->input('type_range_program');
        $programSlide     = $this->storeProgramSlide($slide, $request);

        if ($typeRangeProgram == ModelConst::PROGRAM_RANGE_DEFINED_DATE || $typeRangeProgram ==
        ModelConst::PROGRAM_RANGE_CURRENT_DATE) {
            $this->storeDefinedRange($request, $programSlide);
        }

    }

    private function storeProgramSlide(Slide $slide, Request $request)
    {
        $programSlide = new ProgramSlide();
        $programSlide->fill($request->validated());
        $programSlide->id_slide = $slide->id_slide;
        $programSlide->save();

        return $programSlide;
    }

    private function storeDefinedRange(Request $request, $programSlide)
    {
        if ($request->input('period_current_program') == ModelConst::PROGRAM_PERIOD_DATE) {
            $dates = $request->input('dates');
            $dates = collect($dates);
            $dates = $dates->map($this->mapSetDateCurrentProgramTransform($programSlide));

            DateProgram::insert($dates->toArray());

        }

        if ($request->input('period_current_program') == ModelConst::PROGRAM_PERIOD_DAY) {
            $dates = $request->input('dates');
            $dates = collect($dates);
            $dates = $dates->map($this->mapSetDayCurrentProgramTransform($programSlide));

            DateProgram::insert($dates->toArray());
        }

    }

    private function mapSetDateCurrentProgramTransform($programSlide): callable
    {
        return static function ($item, $key) use ($programSlide) {
            $newItem = [];

            $newItem[ 'id_program' ] = $programSlide->id_program;
            $newItem[ 'date_init' ]  = $item[ 'date_init' ];
            $newItem[ 'date_end' ]   = $item[ 'date_end' ];
            $newItem['created_at']   = Carbon::now();
            $newItem['updated_at']   = Carbon::now();

            return $newItem;
        };
    }

    private function mapSetDayCurrentProgramTransform($programSlide): callable
    {
        return static function ($item, $key) use ($programSlide) {
            $newItem = [];

            $newItem[ 'id_program' ] = $programSlide->id_program;
            $newItem[ 'day_init' ]   = $item[ 'day_init' ];
            $newItem[ 'day_end' ]    = $item[ 'day_end' ];
            $newItem['created_at']   = Carbon::now();
            $newItem['updated_at']   = Carbon::now();

            return $newItem;
        };
    }
}
