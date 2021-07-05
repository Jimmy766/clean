<?php

namespace App\Core\Countries\Transforms;

use App\Core\Countries\Models\Country;
use League\Fractal\TransformerAbstract;

/**
 *   @SWG\Definition(
 *     definition="Country",
 *     required={"identifier","name","iso"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       format="int32",
 *       description="ID elements identifier",
 *       example="305"
 *     ),
 *     @SWG\Property(
 *       property="name",
 *       type="string",
 *       description="Name of contry",
 *       example="United States"
 *     ),
 *     @SWG\Property(
 *       property="iso",
 *       type="string",
 *       description="ISO Alpha 2",
 *       example="US"
 *     )
 *   ),
 */

class CountryTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(Country $country) {
        return [
            'identifier' => (integer)$country->country_id,
            'iso' => (string)$country->country_Iso,
            'name' => (string)$country->name,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'country_id',
            'iso' => 'country_Iso',
            'name' => 'name',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'country_id' => 'identifier',
            'country_Iso' => 'iso',
            'name' => 'name',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
