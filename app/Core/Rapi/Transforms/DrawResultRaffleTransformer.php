<?php

namespace App\Core\Rapi\Transforms;

use App\Core\Raffles\Models\DrawResultRaffle;
use League\Fractal\TransformerAbstract;

/**
 *   @SWG\Definition(
 *     definition="ResultRaffle",
 *     required={"identifier","name"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       format="int32",
 *       description="ID elements identifier",
 *       example="6"
 *     ),
 *     @SWG\Property(
 *       property="result",
 *       type="string",
 *       description="Result raffle",
 *       example="BXD890843"
 *     ),
 *   )
 */

class DrawResultRaffleTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(DrawResultRaffle $draw_result_raffle) {
        return [
            'identifier' => (integer)$draw_result_raffle->id,
            'result' => $draw_result_raffle->result,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'id',
            'result' => 'result',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'id' => 'identifier',
            'result' => 'result',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
