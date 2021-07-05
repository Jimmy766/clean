<?php

namespace App\Core\Syndicates\Transforms;

use App\Core\Syndicates\Models\SyndicateParticipation;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="SyndicateParticipation",
 *     @SWG\Property(
 *       property="draw",
 *       type="object",
 *       description="Draw",
 *       @SWG\Property(property="identifier", type="integer", description="Draw Id", example="1887"),
 *       @SWG\Property(property="date", type="string", format="date", description="Draw date", example="2018-03-24"),
 *       @SWG\Property(
 *         property="results",
 *         description="Draw Results",
 *         type="object",
 *         allOf={ @SWG\Schema(ref="#/definitions/ResultDraw"), }
 *       ),
 *     ),
 *     @SWG\Property(
 *       property="tickets",
 *       type="array",
 *       description="Tickets played",
 *       @SWG\Items(ref="#/definitions/Ticket"),
 *     ),
 *  ),
 */

class SyndicateParticipationTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(SyndicateParticipation $syndicate_participation) {
        return [
            'draw' => $syndicate_participation->draw,
            'tickets' => $syndicate_participation->tickets(),
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
