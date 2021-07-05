<?php

namespace App\Core\Raffles\Resources;

use App\Core\Raffles\Resources\RaffleTierResource;
use App\Core\Base\Traits\UtilsFormatText;
use Illuminate\Http\Resources\Json\JsonResource;
use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *     definition="DatesResultRaffles",
 *     required={"identifier","name"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="dateResult identifier",
 *       example="3"
 *     ),
 *     @SWG\Property(
 *       property="name",
 *       type="string",
 *       description="Name of raffle draw",
 *       example="Trillonario"
 *     ),
 *     @SWG\Property(
 *       property="date",
 *       type="string",
 *       description="date raffle draw",
 *       example="2020-01-01"
 *     ),
 *     @SWG\Property(
 *       property="datesResultRaffles",
 *       description="info date to raffles",
 *       type="object",
 *       allOf={
 *         @SWG\Schema(ref="#/definitions/RaffleTier"),
 *       }
 *     ),
 *  ),
 */
class DatesResultRafflesResource extends JsonResource
{

    use UtilsFormatText;
    public function toArray($request)
    {
        return [
            'id'         => $this->rff_id,
            'name'       => $this->convertTextCharset($this->rff_name),
            'date'       => $this->rff_playdate,
            'raffleTier' => new RaffleTierResource($this->whenLoaded('raffleTier')),
        ];
    }
}
