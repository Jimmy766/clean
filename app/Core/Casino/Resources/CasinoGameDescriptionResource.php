<?php

namespace App\Core\Casino\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasinoGameDescriptionResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'name' => (string)$this->name,
            'description' => (string)$this->description,
            'text' => (string)$this->how_to_win,
        ];
    }
}
