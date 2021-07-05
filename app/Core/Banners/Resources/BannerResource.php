<?php

namespace App\Core\Banners\Resources;

use App\Core\Banners\Resources\ConfigBannerResource;
use App\Core\Countries\Resources\RegionRapiResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *     definition="Banner",
 *     required={"identifier","regions" ,*    },
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="Banner identifier",
 *       example="3"
 *     ),
 *
 *     @SWG\Property(
 *       property="name",
 *       type="string",
 *       example="name banner"
 *     ),
 *     @SWG\Property(
 *       property="status",
 *       type="integer",
 *       example=1
 *     ),
 *     @SWG\Property(
 *       property="type",
 *       type="integer",
 *       example=1
 *     ),
 *     @SWG\Property(
 *       property="type_product",
 *       type="integer",
 *       example=1
 *     ),
 *     @SWG\Property(
 *       property="active",
 *       type="integer",
 *       example=1
 *     ),
 *     @SWG\Property(
 *       property="image",
 *       type="string",
 *       example="https://rapi-reports-stage-public.s3.eu-central-1.amazonaws.com/slides/dashboard1599062424.png"
 *     ),
 *     @SWG\Property(
 *       property="link",
 *       type="string",
 *       example="https://google.com/q?=test-banner-link"
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
 *       property="configBanner",
 *       description="Config Banner",
 *       type="object",
 *       allOf={
 *         @SWG\Schema(ref="#/definitions/ConfigBanner"),
 *       }
 *     ),
 *  ),
 */
class BannerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id_banner' => $this->id_banner,
            'name' => $this->name,
            'status' => $this->status,
            'active' => $this->active,
            'type' => $this->type,
            'type_product' => $this->type_product,
            'image' => $this->image,
            'link' => $this->link,
            'regions' => RegionRapiResource::collection(
                $this->whenLoaded('regions')
            ),
            'configBanner' => ConfigBannerResource::collection(
                $this->whenLoaded('configBanner')
            ),
        ];
    }
}
