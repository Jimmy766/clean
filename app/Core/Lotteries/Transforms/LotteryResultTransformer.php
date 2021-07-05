<?php

namespace App\Core\Lotteries\Transforms;

use App\Core\Lotteries\Models\Lottery;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="LotteryResult",
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="Lottery identifier",
 *       example="3"
 *     ),
 *     @SWG\Property(
 *       property="name",
 *       type="string",
 *       description="Name of lottery",
 *       example="Powerball"
 *     ),
 *     @SWG\Property(
 *       property="currency",
 *       type="string",
 *       description="Currency",
 *       example="USD"
 *     ),
 *     @SWG\Property(
 *       property="region",
 *       description="Region of lottery",
 *       type="object",
 *       allOf={
 *         @SWG\Schema(ref="#/definitions/Region"),
 *       }
 *     ),
 *  ),
 */

class LotteryResultTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */

    public static function transform(Lottery $lottery) {
        $result = [
            'identifier' => (integer)$lottery->lot_id,
            'name' => (string)$lottery->name,
            'currency' => (string)$lottery->currency,
            'region' => $lottery->region_attributes,
        ];
        return $result;
    }

    public static function originalAttribute($index) {
        $attributes = [
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [

        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
