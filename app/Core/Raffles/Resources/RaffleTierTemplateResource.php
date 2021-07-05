<?php

namespace App\Core\Raffles\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *     definition="raffleTierTemplates",
 *     required={"identifier","name"},
 *     @SWG\Property(
 *       property="id",
 *       type="integer",
 *       description="raffle tier template identifier",
 *       example="3"
 *     ),
 *     @SWG\Property(
 *       property="prize",
 *       type="integer",
 *       description="prize raffle tier",
 *       example=1
 *     ),
 *  ),
 */
class RaffleTierTemplateResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "prize" => $this->prize,
            "value" => $this->value,
        ];
    }
}
