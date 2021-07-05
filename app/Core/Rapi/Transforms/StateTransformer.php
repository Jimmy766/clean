<?php

namespace App\Core\Rapi\Transforms;

use App\Core\Rapi\Models\State;
use League\Fractal\TransformerAbstract;

/**
 *   @SWG\Definition(
 *     definition="State",
 *     required={"identifier","name","iso"},
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
 *       description="Name of state",
 *       example="California"
 *     ),
 *     @SWG\Property(
 *       property="iso",
 *       type="string",
 *       description="ISO Alpha 2",
 *       example="CA"
 *     ),
 *     @SWG\Property(
 *       property="country",
 *       type="object",
 *       allOf={
 *         @SWG\Schema(ref="#/definitions/Country"),
 *       }
 *     ),
 *   )
 */

class StateTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(State $state) {
        return [
            'identifier' => (integer)$state->state_id,
            'name' => (string)$state->state_name,
            'iso' => (string)$state->state_iso,
            'country' => $state->country_attributes,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'state_id',
            'name' => 'state_name',
            'iso' => 'state_iso',
            'country' => 'country_attributes',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'state_id' => 'identifier',
            'state_name' => 'name',
            'state_iso' => 'iso',
            'country_attributes' => 'country',

        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
