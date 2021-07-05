<?php

namespace App\Core\Rapi\Transforms;

use App\Core\Rapi\Models\AlertMailsData;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="AlertMailsData",
 *     @SWG\Property(
 *       property="result",
 *       type="boolean",
 *       description="Alert for results of a lottery",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="jackpot",
 *       type="boolean",
 *       description="Alert for Jackpot of a lottery",
 *       example="0"
 *     )
 *  ),
 */
class AlertMailsDataTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */

    public static function transform(AlertMailsData $alertMailsData) {
        $result = [
            'result' => (int)$alertMailsData->send_results,
            'jackpot' => (int)$alertMailsData->send_jackpot,
        ];
        return $result;
    }

    public static function originalAttribute($index) {
        $attributes = [
            'lottery' => 'lot_id',
            'result' => 'send_results',
            'jackpot' => 'send_jackpot',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'lot_id' => 'lottery',
            'send_results' => 'result',
            'send_jackpot' => 'jackpot',

        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
