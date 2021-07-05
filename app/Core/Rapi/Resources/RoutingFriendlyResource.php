<?php

namespace App\Core\Rapi\Resources;

use App\Core\Base\Traits\UtilsFormatText;
use Illuminate\Http\Resources\Json\JsonResource;
use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *     definition="RoutingFriendly",
 *     @SWG\Property(
 *       property="id",
 *       type="integer",
 *       description="Routing id",
 *       example="3"
 *     ),
 *     @SWG\Property(
 *       property="id_product",
 *       type="integer",
 *       description="id of product",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="type_product",
 *       type="integer",
 *       description="type product",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="partial_path",
 *       type="string",
 *       description="partial path route",
 *       example="/lotteries"
 *     ),
 *     @SWG\Property(
 *       property="language",
 *       type="string",
 *       description="language",
 *       example="/lotteries"
 *     ),
 *  ),
 */
class RoutingFriendlyResource extends JsonResource
{
    use UtilsFormatText;

    public function toArray($request)
    {
        return [
            'id'           => $this->id,
            'id_product'   => $this->element_id,
            'type_product' => $this->element_type,
            'partial_path' => $this->convertTextCharset($this->element_name),
            'language'     => $this->lang,
        ];
    }
}
