<?php

namespace App\Core\Casino\Resources;

use App\Core\Casino\Resources\CasinoGameResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CasinoCategoryResource extends JsonResource
{
    /**
     *   @SWG\Definition(
     *     definition="CasinoCategoryResponse",
     *     required={"identifier","name","games"},
     *     @SWG\Property(
     *       property="category_name",
     *       type="string",
     *       description="Name of Category",
     *       example="Slots"
     *     ),
     *     @SWG\Property(
     *       property="category_tag",
     *       type="string",
     *       description="Tag of Category",
     *       example="#CASINO_SLOT_CATEGORY#"
     *     ),
     *   )
     *
     */
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'identifier' => (integer)$this->id,
            'category_name'=>(string)$this->name,
            'category_tag'=>"#".(string)$this->tag_name."#",
            'games'=>CasinoGameResource::collection($this->whenLoaded('casino_games_category_clients'))
        ];
    }
}
