<?php

namespace App\Core\Slides\Resources;

use App\Core\Assets\Resources\AssetResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *     definition="ImageSlide",
 *     required={"identifier", "image", "type"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="ImageSlide identifier",
 *       example="3"
 *     ),
 *     @SWG\Property(
 *       property="image",
 *       type="string",
 *       description="Image url",
 *       example="https://rapi-reports-stage-public.s3.eu-central-1.amazonaws.com/slides/dashboard1599062424.png"
 *     ),
 *     @SWG\Property(
 *       property="type",
 *       type="integer",
 *       description="Type Image",
 *       example="1"
 *     ),
 *  ),
 */
class ImageSlideResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id_image' => $this->id_image,
            'image'    => $this->image,
            'id_asset' => $this->id_asset,
            'asset'    => new AssetResource($this->whenLoaded('asset')),
        ];
    }
}
