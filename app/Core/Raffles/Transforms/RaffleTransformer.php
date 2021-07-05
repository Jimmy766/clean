<?php

namespace App\Core\Raffles\Transforms;

use App\Core\AdminLang\Services\AL;
use App\Core\Raffles\Models\Raffle;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="Raffle",
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="Raffle identifier",
 *       example="3"
 *     ),
 *     @SWG\Property(
 *       property="draw_identifier",
 *       type="integer",
 *       description="Raffle draw identifier",
 *       example="3"
 *     ),
 *     @SWG\Property(
 *       property="draw_extra_identifier",
 *       type="integer",
 *       description="Raffle identifier",
 *       example="3"
 *     ),
 *     @SWG\Property(
 *       property="name",
 *       type="string",
 *       description="Raffle name",
 *       example="Summer Gordo Raffle"
 *     ),
 *     @SWG\Property(
 *       property="type_tag",
 *       type="string",
 *       description="Raffle type tag",
 *       example="#LOTERIA_NACIONAL_RAFFLE_TYPE1#"
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
 *       property="jackpot_in_usd",
 *       type="integer",
 *       description="Value of jackpot in USD",
 *       example="1100000"
 *     ),
 *     @SWG\Property(
 *       property="date",
 *       type="string",
 *       format="date-time",
 *       description="Raffle draw date",
 *       example="2018-01-06 12:00:00"
 *     ),
 *     @SWG\Property(
 *       property="prices",
 *       type="array",
 *       @SWG\Items(ref="#/definitions/RafflePrice")
 *     ),
 *  ),
 */

class RaffleTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(Raffle $raffle) {
        return [
            'identifier' => (integer)$raffle->inf_id,
            'draw_identifier' => $raffle->draw_id,
            'draw_extra_identifier' => $raffle->draw_extra_id,
            'name' => $raffle->inf_name,
            'printable_name' => $raffle->inf_name,
            'name_tag' =>AL::translate( $raffle->name,app()->getLocale()),
            'type_tag' => $raffle->type_tag,
            'currency' => $raffle->draw_curr_code,
            'jackpot' => $raffle->jackpot,
            'jackpot_in_usd' => $raffle->jackpot_usd,
            'date' => $raffle->date,
            'prices' => $raffle->prices,
            'routing_friendly' => $raffle->routing_friendly_attributes,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'date' => 'date',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'date' => 'date',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
