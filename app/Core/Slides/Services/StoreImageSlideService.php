<?php

namespace App\Core\Slides\Services;

use App\Core\Slides\Models\ImageSlide;
use App\Core\Slides\Models\Slide;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Class StoreImageSlideService
 * @package App\Services
 */
class StoreImageSlideService
{

    /**
     * @param Slide   $slide
     * @param Request $request
     */
    public function execute(Slide $slide, Request $request): void
    {
        $requestValidated = $request->validated();
        $images           = array_key_exists('images', $requestValidated) ? $requestValidated[ 'images' ]
            : [];
        $images           = collect($images);

        $images->map($this->mapSetSlideToSlideImageTransform($slide));
    }

    /**
     * @param $slide
     * @return callable
     */
    private function mapSetSlideToSlideImageTransform(Slide $slide): callable
    {
        return static function ($item, $key) use ($slide) {
            $newItem[ 'id_slide' ]   = $slide->id_slide;
            $newItem[ 'created_at' ] = Carbon::now();
            $newItem[ 'updated_at' ] = Carbon::now();
            $newItem[ 'image' ]      = $item[ 'image' ];

            if (array_key_exists('id_asset', $item)) {
                $newItem[ 'id_asset' ] = $item[ 'id_asset' ];
            }

            ImageSlide::create($newItem);

            return $newItem;
        };
    }

}
