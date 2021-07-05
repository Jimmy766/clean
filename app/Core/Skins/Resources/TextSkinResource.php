<?php

namespace App\Core\Skins\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *     definition="TextSkin",
 *     required={"identifier", "tag", "text"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="TextSkin identifier",
 *       example="3"
 *     ),
 *     @SWG\Property(
 *       property="tag",
 *       type="string",
 *       description="tag Text",
 *       example="text banner"
 *     ),
 *     @SWG\Property(
 *       property="text",
 *       type="string",
 *       description="Text url",
 *       example="log text describe values"
 *     ),
 *  ),
 */
class TextSkinResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id_text' => $this->id_text,
            'tag'     => $this->tag,
            'text'    => $this->text,
        ];
    }
}
