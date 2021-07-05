<?php

namespace App\Core\Slides\Services;

use App\Core\Slides\Models\ConfigSlide;
use App\Core\Base\Traits\TextUtilsTraits;
use App\Core\Slides\Models\Slide;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Class StoreConfigSlideService
 * @package App\Services
 */
class StoreConfigSlideService
{
    use TextUtilsTraits;

    /**
     * @param Slide   $slide
     * @param Request $request
     */
    public function execute(Slide $slide, Request $request)
    {
        $config = $request->config;
        $config = collect($config);

        $config = $config->map($this->mapSetSlideToConfigTransform($slide));
        $config = $config->toArray();
        ConfigSlide::insert($config);
    }

    private function mapSetSlideToConfigTransform(Slide $slide): callable
    {
        return function ($item, $key) use ($slide) {
            $newItem = [];

            $newItem[ 'id_slide' ]       = $slide->id_slide;
            $newItem[ 'title' ]          = $item[ 'title' ];
            $newItem[ 'subtitle' ]       = $this->checkOrEmpty($item, 'subtitle');
            $newItem[ 'text_promotion' ] = $this->checkOrEmpty($item, 'text_promotion');
            $newItem[ 'description' ]    = $this->checkOrEmpty($item, 'description');
            $newItem[ 'url' ]            = $item[ 'url' ];
            $newItem[ 'id_language' ]    = $item[ 'id_language' ];
            $newItem[ 'created_at' ]     = Carbon::now();
            $newItem[ 'updated_at' ]     = Carbon::now();

            return $newItem;
        };
    }
}
