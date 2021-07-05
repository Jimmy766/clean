<?php

namespace App\Core\Slides\Resources;

use App\Core\Languages\Resources\LanguageResource;
use App\Core\Base\Traits\UtilsFormatText;
use Illuminate\Http\Resources\Json\JsonResource;
use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *     definition="ConfigSlide",
 *     required={"identifier", "name", "description", "url", "datePrograms"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="ProgramSlide identifier",
 *       example="25"
 *     ),
 *     @SWG\Property(
 *       property="title",
 *       type="string",
 *       example="trillonario lottery"
 *     ),
 *     @SWG\Property(
 *       property="subtitle",
 *       type="string",
 *       example="lottery much money"
 *     ),
 *     @SWG\Property(
 *       property="description",
 *       type="string",
 *       example="description"
 *     ),
 *     @SWG\Property(
 *       property="text_promtion",
 *       type="string",
 *       example="01-05-2020"
 *     ),
 *     @SWG\Property(
 *       property="url",
 *       type="string",
 *       description="Url",
 *       example="0"
 *     ),
 *     @SWG\Property(
 *       property="language",
 *       description="Language Slide",
 *       type="object",
 *       allOf={
 *           @SWG\Schema(ref="#/definitions/ImageSlide")
 *       }
 *     ),
 *  ),
 */
class ConfigSlideResource extends JsonResource
{
    use UtilsFormatText;

    public function toArray($request)
    {
        return [
            'title'          => $this->title,
            'subtitle'       => $this->subtitle,
            'description'    => $this->description,
            'text_promotion' => $this->text_promotion,
            'url'            => $this->url,

            'language' => new LanguageResource($this->whenLoaded('language')),
        ];
    }
}
