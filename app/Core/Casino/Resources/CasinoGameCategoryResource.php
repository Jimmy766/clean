<?php

namespace App\Core\Casino\Resources;

use App\Core\Casino\Resources\CasinoGameResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CasinoGameCategoryResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'is_popular' => (integer)$this->popular_game,
            'game' => new CasinoGameResource($this->whenLoaded('casino_game'))
        ];
    }
}
