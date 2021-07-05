<?php

namespace App\Core\Syndicates\Transforms;

use App\Core\Syndicates\Models\SyndicateRaffle;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="SyndicateRaffleList",
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="Syndicate Raffle identifier",
 *       example="3"
 *     ),
 *     @SWG\Property(
 *       property="name",
 *       type="string",
 *       description="Syndicate Raffle name",
 *       example="#GROUP_SUMMER_GORDO#"
 *     ),
 *     @SWG\Property(
 *       property="currency",
 *       type="string",
 *       description="Currency",
 *       example="USD"
 *     ),
 *     @SWG\Property(
 *       property="jackpot",
 *       type="integer",
 *       description="Value of jackpot",
 *       example="1000000"
 *     ),
 *     @SWG\Property(
 *       property="date",
 *       type="string",
 *       format="date-time",
 *       description="Syndicate Raffle draw date",
 *       example="2018-01-06 12:00:00"
 *     ),
 *  ),
 */

class SyndicateRaffleListTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(SyndicateRaffle $syndicate_raffle) {
        return [
            'identifier' => (integer)$syndicate_raffle->id,
            'name' => $syndicate_raffle->syndicate_raffle_name,
            'jackpot' => $syndicate_raffle->jackpot,
            'currency' => $syndicate_raffle->currency,
            'date' => $syndicate_raffle->date,
        ];
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
