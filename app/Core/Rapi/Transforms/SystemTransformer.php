<?php

namespace App\Core\Rapi\Transforms;

use App\Core\Rapi\Models\System;
use League\Fractal\TransformerAbstract;

/**
 *   @SWG\Definition(
 *     definition="System",
 *     required={"identifier","name","iso"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="ID system identifier",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="name",
 *       type="string",
 *       description="Name of contry",
 *       example="Trillonario"
 *     ),
 *     @SWG\Property(
 *       property="code",
 *       type="string",
 *       description="Abbreviation of system",
 *       example="TRI"
 *     )
 *   ),
 */

class SystemTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(System $system) {
        return [
            'identifier' => (integer)$system->sys_id,
            'name' => (string)$system->sys_name,
            'code' => (string)$system->code,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'sys_id',
            'name' => 'sys_name',
            'code' => 'code',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'sys_id' => 'identifier',
            'sys_name' => 'name',
            'code' => 'code',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
