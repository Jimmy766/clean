<?php

namespace App\Core\Countries\Transforms;

use App\Core\Countries\Models\Continent;
use League\Fractal\TransformerAbstract;

/**
 *   @SWG\Definition(
 *     definition="Continent",
 *     required={"identifier","name"},
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
 *       example="North America"
 *     ),
 *   )
 */

class ContinentTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(Continent $continent) {
        return [
            'identifier' => (integer)$continent->cont_id,
            'name' => $continent->name,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'cont_id',
            'name' => 'name',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'cont_id' => 'identifier',
            'name' => 'name',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
