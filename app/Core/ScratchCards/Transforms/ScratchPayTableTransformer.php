<?php

namespace App\Core\ScratchCards\Transforms;

use App\Core\ScratchCards\Models\ScratchCardPayTable;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="ScratchCardPayTable",
 *     @SWG\Property(
 *       property="tier",
 *       type="integer",
 *       format="int32",
 *       description="Tier number",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="winners",
 *       type="integer",
 *       format="int32",
 *       description="Winner",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="prize",
 *       type="number",
 *       format="float",
 *       description="Prize if win",
 *       example="1.0"
 *     ),
 *     @SWG\Property(
 *       property="currency",
 *       type="string",
 *       description="Currency",
 *       example="USD"
 *     ),
 *   ),
 */

class ScratchPayTableTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(ScratchCardPayTable $scratch_card_paytable) {
        return [
            'tier' => (integer)$scratch_card_paytable->paytable_tier,
            'winners' => (integer)$scratch_card_paytable->paytable_winners,
            'prize' => (float)$scratch_card_paytable->paytable_prize,
            'currency' => $scratch_card_paytable->curr_code,
        ];
    }

    /**
     * @param $index
     *
     * @return mixed|null
     */
    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'paytable_id',
            'tier' => 'paytable_tier',
            'winners' => 'paytable_winners',
            'prize' => 'paytable_prize',
            'currency' => 'curr_code',
        ];
        return isset($attributes[ $index ]) ? $attributes[ $index ] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'paytable_id' => 'identifier',
            'paytable_tier' => 'tier',
            'paytable_winners' => 'winners',
            'paytable_prize' => 'prize',
            'curr_code' => 'currency',
        ];
        return isset($attributes[ $index ]) ? $attributes[ $index ] : null;
    }
}
