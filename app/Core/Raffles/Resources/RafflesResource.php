<?php

namespace App\Core\Raffles\Resources;

use App\Core\Raffles\Resources\DatesResultRafflesResource;
use App\Core\Base\Traits\UtilsFormatText;
use Illuminate\Http\Resources\Json\JsonResource;
use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *     definition="RaffleResult",
 *     required={"identifier","name", },
 *     @SWG\Property(
 *       property="id",
 *       type="integer",
 *       description="raffle identifier",
 *       example="3"
 *     ),
 *     @SWG\Property(
 *       property="name",
 *       type="string",
 *       description="Name of asset",
 *       example="Trillonario"
 *     ),
 *     @SWG\Property(
 *       property="datesResultRaffles",
 *       description="info date to raffles",
 *       type="object",
 *       allOf={
 *         @SWG\Schema(ref="#/definitions/DatesResultRaffles"),
 *       }
 *     ),
 *  ),
 */
class RafflesResource extends JsonResource
{
    use UtilsFormatText;

    public function toArray($request)
    {
        return [
            'identifier'         => $this->inf_id,
            'name'               => $this->convertTextCharset($this->name),
            'date'               => $this->format_date,
            'datesResultRaffles' => new DatesResultRafflesResource($this->whenLoaded('datesResultRaffles')),
        ];
    }
}
