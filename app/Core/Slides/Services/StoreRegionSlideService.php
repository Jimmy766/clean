<?php

namespace App\Core\Slides\Services;

use App\Core\Slides\Models\RegionSlidePivot;
use App\Core\Slides\Models\Slide;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StoreRegionSlideService
{

    public function execute(Slide $slide, Request $request)
    {
        $regions = $request->input('regions');
        $regions = collect($regions);

        $regions = $regions->map($this->mapSetSlideToRegionTransform($slide));

        RegionSlidePivot::insert($regions->toArray());
    }

    private function mapSetSlideToRegionTransform(Slide $slide): callable
    {
        return static function ($item, $key) use ($slide) {

            $item[ 'id_slide' ]   = $slide->id_slide;
            $item[ 'created_at' ] = Carbon::now();
            $item[ 'updated_at' ] = Carbon::now();

            return $item;
        };
    }

}
