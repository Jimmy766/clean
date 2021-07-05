<?php

namespace App\Core\Slides\Resources;

use App\Core\Slides\Resources\ConfigSlideResource;
use App\Core\Slides\Resources\ImageSlideResource;
use App\Core\Slides\Resources\ProgramSlideResource;
use App\Core\Countries\Resources\RegionRapiResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *     definition="Slide",
 *     required={"identifier","jack_pot", "regions",*    },
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="Slide identifier",
 *       example="3"
 *     ),
 *     @SWG\Property(
 *       property="jack_pot",
 *       type="string",
 *       description="Jack Pot",
 *       example="2"
 *     ),
 *     @SWG\Property(
 *       property="status",
 *       type="string",
 *       description="status",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="active",
 *       type="string",
 *       description="active",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="regions",
 *       description="regions of region",
 *       type="array",
 *       @SWG\Items(
 *          type="object",
 *          allOf={
 *              @SWG\Schema(ref="#/definitions/RegionRapi"),
 *          }
 *        )
 *     ),
 *     @SWG\Property(
 *       property="programSlide",
 *       description="Program Slide",
 *       type="object",
 *       allOf={
 *         @SWG\Schema(ref="#/definitions/ProgramSlide"),
 *       }
 *     ),
 *     @SWG\Property(
 *       property="configSlide",
 *       description="Config Slide",
 *       type="array",
 *       @SWG\Items(
 *          type="object",
 *          allOf={
 *              @SWG\Schema(ref="#/definitions/ConfigSlide"),
 *          }
 *        )
 *     ),
 *     @SWG\Property(
 *       property="images",
 *       description="Images Slide",
 *       type="array",
 *       @SWG\Items(
 *          type="object",
 *          allOf={
 *              @SWG\Schema(ref="#/definitions/ImageSlide"),
 *          }
 *        )
 *     ),
 *  ),
 */
class SlideResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id_slide'     => $this->id_slide,
            'name'         => $this->name,
            'jack_pot'     => $this->jack_pot,
            'status'       => $this->status,
            'active'       => $this->active,
            'play_game_at' => $this->play_game_at,
            'type_slide'   => $this->type_slide,
            'regions'      => \App\Core\Countries\Resources\RegionRapiResource::collection($this->whenLoaded('regions')),
            'programSlide' => new ProgramSlideResource($this->whenLoaded('programSlide')),
            'configsSlide' => ConfigSlideResource::collection($this->whenLoaded('configSlide')),
            'images'       => ImageSlideResource::collection($this->whenLoaded('images')),
        ];
    }
}
