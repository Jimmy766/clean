<?php

namespace App\Core\Syndicates\Transforms;

use App\Core\AdminLang\Services\AL;
use App\Core\Syndicates\Models\SyndicateRaffle;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="RaffleSyndicate",
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
 *     @SWG\Property(
 *       property="tickets_qty",
 *       type="integer",
 *       description="Tickets to show",
 *       example="40"
 *     ),
 *     @SWG\Property(
 *       property="prices",
 *       type="array",
 *       @SWG\Items(ref="#/definitions/SyndicateRafflePrice")
 *     ),
 *  ),
 */

class SyndicateRaffleTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(SyndicateRaffle $syndicate_raffle) {
        return [
            'identifier' => (integer)$syndicate_raffle->id,
            'name' => AL::translate("SYNDICATE_RAFFLE_NAME_".$syndicate_raffle->name),
            'printable_name' => $syndicate_raffle->printable_name,
            'tag_name' => $syndicate_raffle->syndicate_raffle_name,
            'tag_name_short' => $syndicate_raffle->syndicate_raffle_name,
            'currency' => $syndicate_raffle->currency,
            'jackpot' => $syndicate_raffle->jackpot,
            'date' => $syndicate_raffle->date,
            'tickets_qty' => $syndicate_raffle->tickets_to_show,
            'fractions' => $syndicate_raffle->participations_fractions,
            'prices' => $syndicate_raffle->prices,
            'routing_friendly' => $syndicate_raffle->routing_friendly_attributes,
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
