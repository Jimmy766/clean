<?php

namespace App\Core\Countries\Resources;

use App\Core\Countries\Resources\CountryResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class CountryRegionResource
 * @package App\Http\Resources
 */
class CountryRegionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            "id_country_region" => $this->id_country_region,
            "id_country"        => $this->id_country,
            "id_region"         => $this->id_region,
            'country'           => new CountryResource($this->whenLoaded('country')),
        ];
    }
}
