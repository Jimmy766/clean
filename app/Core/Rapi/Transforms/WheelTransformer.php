<?php

namespace App\Core\Rapi\Transforms;

use App\Core\Rapi\Models\Wheel;
use League\Fractal\TransformerAbstract;

/**
 *   @SWG\Definition(
 *     definition="Wheel",
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="Wheel Id",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="type",
 *       type="string",
 *       description="Wheel type",
 *       example="Abbreviated"
 *     ),
 *     @SWG\Property(
 *       property="balls",
 *       type="integer",
 *       description="Balls to play",
 *       example="5"
 *     ),
 *     @SWG\Property(
 *       property="tickets",
 *       type="integer",
 *       description="Tickets to play",
 *       example="5"
 *     )
 *   ),
 */


class WheelTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(Wheel $wheel) {
        return [
            'identifier' => $wheel->wheel_id,
            'type' => $wheel->type,
            'balls' => $wheel->wheel_balls,
            'tickets' => $wheel->wheel_lines,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            "wheel_balls" => "balls"
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            "balls" => "wheel_balls"
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
