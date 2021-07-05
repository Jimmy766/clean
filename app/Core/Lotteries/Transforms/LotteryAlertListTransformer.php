<?php

namespace App\Core\Lotteries\Transforms;

use App\Core\Lotteries\Models\Lottery;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="LotteryAlertList",
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="Lottery identifier",
 *       example="3"
 *     ),
 *     @SWG\Property(
 *       property="name",
 *       type="string",
 *       description="Name of live lottery",
 *       example="Powerball"
 *     ),
 *     @SWG\Property(
 *       property="region",
 *       description="Region of lottery",
 *       type="object",
 *       allOf={
 *         @SWG\Schema(ref="#/definitions/Region"),
 *       }
 *     ),
 *     @SWG\Property(
 *       property="has_alert",
 *       description="Show if the lottery has an alert for the logged user",
 *       type="object",
 *       allOf={
 *         @SWG\Schema(ref="#/definitions/AlertMailsData"),
 *       }
 *     ),
 *  ),
 */

class LotteryAlertListTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */

    public static function transform(Lottery $lottery) {
        $result = [
            'identifier' => (integer)$lottery->lot_id,
            'name' => (string)$lottery->lot_name_en,
            'region' => $lottery->region_attributes,
            'has_alert' => $lottery->alert_mails,
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
