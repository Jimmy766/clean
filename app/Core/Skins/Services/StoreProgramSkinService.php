<?php

namespace App\Core\Skins\Services;

use App\Core\Base\Classes\ModelConst;
use App\Core\Skins\Models\DateProgramsSkin;
use App\Core\Skins\Models\ProgramSkin;
use App\Core\Skins\Models\Skin;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StoreProgramSkinService
{

    public function execute(Skin $skin, Request $request)
    {
        $typeRangeProgram = $request->input('type_range_program');
        $programSkin      = $this->storeProgramSkin($skin, $request);

        if ($typeRangeProgram == ModelConst::PROGRAM_RANGE_DEFINED_DATE || $typeRangeProgram == ModelConst::PROGRAM_RANGE_CURRENT_DATE) {
            $this->storeDefinedRange($request, $programSkin);
        }

    }

    /**
     * @param Skin    $skin
     * @param Request $request
     * @return ProgramSkin
     */
    private function storeProgramSkin(Skin $skin, Request $request): ProgramSkin
    {
        $programSkin = new ProgramSkin();
        $programSkin->fill($request->validated());
        $programSkin->id_skin = $skin->id_skin;
        $programSkin->save();

        return $programSkin;
    }

    /**
     * @param Request $request
     * @param         $programSkin
     */
    private function storeDefinedRange(Request $request, $programSkin): void
    {
        if ($request->input( 'period_current_program' ) == ModelConst::PROGRAM_PERIOD_DATE) {
            $dates = $request->input('dates');
            $dates = collect($dates);
            $dates = $dates->map(
                $this->mapSetDateCurrentProgramTransform($programSkin)
            );

            DateProgramsSkin::insert($dates->toArray());

        }

        if ($request->input( 'period_current_program' ) == ModelConst::PROGRAM_PERIOD_DAY) {
            $dates = $request->input('dates');
            $dates = collect($dates);
            $dates = $dates->map(
                $this->mapSetDayCurrentProgramSkinTransform($programSkin)
            );

            DateProgramsSkin::insert($dates->toArray());
        }

    }

    private function mapSetDateCurrentProgramTransform($programSkin): callable
    {
        return static function ($item, $key) use ($programSkin) {
            $newItem = [];

            $newItem[ 'id_program' ] = $programSkin->id_program;
            $newItem[ 'date_init' ]  = $item[ 'date_init' ];
            $newItem[ 'date_end' ]   = $item[ 'date_end' ];
            $newItem[ 'created_at' ] = Carbon::now();
            $newItem[ 'updated_at' ] = Carbon::now();

            return $newItem;
        };
    }

    private function mapSetDayCurrentProgramSkinTransform($programSkin
    ): callable {
        return static function ($item, $key) use ($programSkin) {
            $newItem = [];

            $newItem[ 'id_program' ] = $programSkin->id_program;
            $newItem[ 'day_init' ]   = $item[ 'day_init' ];
            $newItem[ 'day_end' ]    = $item[ 'day_end' ];
            $newItem[ 'created_at' ] = Carbon::now();
            $newItem[ 'updated_at' ] = Carbon::now();

            return $newItem;
        };
    }
}
