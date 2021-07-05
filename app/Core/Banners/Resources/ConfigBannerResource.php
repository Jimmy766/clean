<?php

namespace App\Core\Banners\Resources;

use App\Core\Languages\Resources\LanguageResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *     definition="ConfigBanner",
 *     required={"identifier", "title", "subtitle", "link","id_language"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="ProgramSlide identifier",
 *       example="25"
 *     ),
 *     @SWG\Property(
 *       property="title",
 *       type="string",
 *       description="Name",
 *       example="2"
 *     ),
 *     @SWG\Property(
 *       property="subtitle",
 *       type="string",
 *       description="Config subtitle",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="id_language",
 *       type="integer",
 *       description="id_language relation with languages",
 *       example="0"
 *     )
 *  ),
 */
class ConfigBannerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id_config_banner' => $this->id_config_banner,

            'title'     => $this->title,
            'subtitle'  => $this->subtitle,
            'languages' => LanguageResource::collection($this->whenLoaded('languages')),
        ];
    }
}
