<?php

namespace App\Core\Casino\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *     definition="Favorite",
 *     required={"id_favorite","id_favoriteable", "type_favoritable",*    },
 *     @SWG\Property(
 *       property="id_favorite",
 *       type="integer",
 *       description="id favorite",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="id_favoriteable",
 *       type="string",
 *       description="id_favoriteable",
 *       example="id model service"
 *     ),
 *     @SWG\Property(
 *       property="type_favorite",
 *       type="string",
 *       description="type favorite",
 *       example="type favorite"
 *     ),
 *  ),
 */
class FavoriteResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id_favorite'     => $this->id_favorite,
            'id_favoriteable' => $this->id_favoriteable,
            'type_favorite'   => $this->type_favorite,
            'favoriteable'    => $this->validateFavoriteableExist(),
        ];
    }

    private function validateFavoriteableExist()
    {
        if (property_exists($this->resource, 'favoriteable')) {
            return $this->favoriteable;
        }

        return $this->whenLoaded('favoriteable');
    }
}
