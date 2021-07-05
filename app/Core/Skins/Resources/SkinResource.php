<?php

namespace App\Core\Skins\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *     definition="Skin",
 *     required={"identifier","name", "image",*    },
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="Skin identifier",
 *       example="3"
 *     ),
 *     @SWG\Property(
 *       property="name",
 *       type="string",
 *       description="Name of skin",
 *       example="Trillonario"
 *     ),
 *     @SWG\Property(
 *       property="status",
 *       type="string",
 *       description="status",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="active",
 *       type="string",
 *       description="active",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="regions",
 *       description="regions of region",
 *       type="object",
 *       allOf={
 *         @SWG\Schema(ref="#/definitions/RegionRapi"),
 *       }
 *     ),
 *     @SWG\Property(
 *       property="programSkin",
 *       description="Program Skin",
 *       type="object",
 *       allOf={
 *         @SWG\Schema(ref="#/definitions/ProgramSkin"),
 *       }
 *     ),
 *     @SWG\Property(
 *       property="configSkin",
 *       description="Config Skin",
 *       type="object",
 *       allOf={
 *         @SWG\Schema(ref="#/definitions/ConfigSkin"),
 *       }
 *     ),
 *  ),
 */
class SkinResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id_skin'     => $this->id_skin,
            'name'        => $this->name,
            'active'      => $this->active,
            'status'      => $this->status,
            'regions'     => \App\Core\Countries\Resources\RegionRapiResource::collection(
                $this->whenLoaded('regions')
            ),
            'programSkin' => ProgramSkinResource::collection(
                $this->whenLoaded('programSkin')
            ),
            'configSkin'  => ConfigSkinResource::collection(
                $this->whenLoaded('configSkin')
            ),
        ];
    }
}
