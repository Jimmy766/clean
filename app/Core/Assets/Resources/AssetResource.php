<?php

namespace App\Core\Assets\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *     definition="Asset",
 *     required={"identifier","name", "image",*    },
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="Asset identifier",
 *       example="3"
 *     ),
 *     @SWG\Property(
 *       property="name",
 *       type="string",
 *       description="Name of asset",
 *       example="Trillonario"
 *     ),
 *     @SWG\Property(
 *       property="image",
 *       type="string",
 *       description="Image of asset",
 *       example="http://amazon.com/url-image"
 *     ),
 *  ),
 */
class AssetResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id_asset' => $this->id_asset,
            'name'      => $this->name,
            'image'     => $this->image,
        ];
    }
}
