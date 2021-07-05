<?php

namespace App\Core\Countries\Resources;

use App\Core\Banners\Resources\BannerResource;
use App\Core\Countries\Resources\CountryRegionResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *     definition="RegionRapi",
 *     required={"identifier","name", "countries",*    },
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="RegionRapi identifier",
 *       example="3"
 *     ),
 *     @SWG\Property(
 *       property="name",
 *       type="string",
 *       description="Name of region",
 *       example="The Caribbean"
 *     ),
 *     @SWG\Property(
 *       property="countries",
 *       description="countries of region",
 *       type="array",
 *       @SWG\Items(
 *          type="object",
 *          allOf={
 *              @SWG\Schema(ref="#/definitions/Country"),
 *          }
 *        )
 *     ),
 *  ),
 */
class RegionRapiResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id_region'         => $this->id_region,
            'name'              => $this->name,
            'countries_regions' => CountryRegionResource::collection($this->whenLoaded('countriesRegions')),
            'banners' => BannerResource::collection($this->whenLoaded('banners')),
        ];
    }
}
