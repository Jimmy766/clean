<?php

namespace App\Core\Countries\Transforms;

use App\Core\Countries\Models\Region;
use League\Fractal\TransformerAbstract;

/**
 *   @SWG\Definition(
 *     definition="Region",
 *     required={"identifier","name","flag","country","continent"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       format="int32",
 *       description="ID elements identifier",
 *       example="6"
 *     ),
 *     @SWG\Property(
 *       property="name",
 *       type="string",
 *       description="Name of Region",
 *       example="California"
 *     ),
 *     @SWG\Property(
 *       property="flag",
 *       type="string",
 *       description="Region flag",
 *       example="CA"
 *     ),
 *     @SWG\Property(
 *       property="country",
 *       type="object",
 *       allOf={
 *         @SWG\Schema(ref="#/definitions/Country"),
 *       }
 *     ),
 *     @SWG\Property(
 *       property="continent",
 *       type="object",
 *       allOf={
 *         @SWG\Schema(ref="#/definitions/Continent"),
 *       }
 *     ),
 *   )
 */

class RegionTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(Region $region) {
        return [
            'identifier' => (integer)$region->reg_id,
            'name' => (string)$region->name,
            'flag' => (string)$region->reg_flag,
            'country' => $region->country_attributes,
            'continent' => $region->continent_attributes,

        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'reg_id',
            'name' => 'name',
            'country' => 'country_id',
            'continent' => 'continent_id',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'reg_id' => 'identifier',
            'name' => 'name',
            'country_id' => 'country',
            'continent_id' => 'continent',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

}
