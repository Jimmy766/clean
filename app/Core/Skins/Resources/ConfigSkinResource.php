<?php

namespace App\Core\Skins\Resources;

use App\Core\Languages\Resources\LanguageResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *     definition="ConfigSkin",
 *     required={"identifier", "name", "description", "url", "datePrograms"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="ProgramSlide identifier",
 *       example="25"
 *     ),
 *     @SWG\Property(
 *       property="name",
 *       type="string",
 *       description="Name",
 *       example="2"
 *     ),
 *     @SWG\Property(
 *       property="description",
 *       type="string",
 *       description="Config Description",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="url",
 *       type="string",
 *       description="Url",
 *       example="0"
 *     ),
 *     @SWG\Property(
 *       property="images",
 *       description="Images related to Config Slide",
 *       type="array",
 *       @SWG\Items(
 *          type="object",
 *          allOf={
 *              @SWG\Schema(ref="#/definitions/ImageSlide"),
 *          }
 *       )
 *     ),
 *  ),
 */
class ConfigSkinResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'name'        => $this->name,
            'description' => $this->description,

            'languages' => new LanguageResource($this->whenLoaded('languages')),

            'files' => FileSkinResource::collection(
                $this->whenLoaded('files')
            ),
            'texts' => TextSkinResource::collection(
                $this->whenLoaded('texts')
            ),
        ];
    }
}
