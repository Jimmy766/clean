<?php

namespace App\Core\Syndicates\Transforms;

use App\Core\Syndicates\Models\SyndicateRaffleParticipation;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="SyndicateRaffleParticipation",
 *     @SWG\Property(
 *       property="draw",
 *       type="object",
 *       description="Draw",
 *       @SWG\Property(property="identifier", type="integer", description="Draw Id", example="1887"),
 *       @SWG\Property(property="date", type="string", format="date", description="Draw date", example="2018-03-24"),
 *     ),
 *     @SWG\Property(
 *       property="tickets",
 *       type="array",
 *       description="Tickets played",
 *       @SWG\Items(ref="#/definitions/RaffleTicket"),
 *     ),
 *  ),
 */

class SyndicateRaffleParticipationTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(SyndicateRaffleParticipation $syndicate_raffle_participation) {
        return [
            'draw' => $syndicate_raffle_participation->raffle_draw,
            'tickets' => $syndicate_raffle_participation->raffle_tickets(),
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'draw' => 'draw',
            'tickets' => 'tickets',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'draw' => 'draw',
            'tickets' => 'tickets',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
