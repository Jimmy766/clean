<?php

namespace App\Core\Raffles\Resources;

use App\Core\Raffles\Resources\RaffleTierTemplateResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *     definition="RaffleTier",
 *     required={"identifier","name"},
 *     @SWG\Property(
 *       property="id",
 *       type="integer",
 *       description="raffle tier identifier",
 *       example="3"
 *     ),
 *     @SWG\Property(
 *       property="active",
 *       type="integer",
 *       description="active raffle tier",
 *       example=1
 *     ),
 *     @SWG\Property(
 *       property="raffleTierTemplates",
 *       description="info date to raffles",
 *       type="array",
 *       @SWG\Items(
 *          type="object",
 *           allOf={
 *             @SWG\Schema(ref="#/definitions/raffleTierTemplates"),
 *           }
 *        )
 *     ),
 *  ),
 */
class RaffleTierResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "active" => $this->active,
            'raffleTierTemplates' => RaffleTierTemplateResource::collection($this->whenLoaded('raffleTierTemplates')),
        ];
    }
}
