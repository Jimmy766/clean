<?php

namespace App\Core\Casino\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasinoGameBetConfigResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'=>$this->id,
            'curr_code'=>$this->curr_code,
            'min_bet'=>$this->min_bet,
            'max_bet'=>$this->max_bet,
        ];
    }
}
