<?php

namespace App\Core\Rapi\Transforms;

use App\Core\Rapi\Models\Ticket;
use League\Fractal\TransformerAbstract;

/**
 *   @SWG\Definition(
 *     definition="Ticket",
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="Ticket Id",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="line_balls",
 *       type="array",
 *       description="Line balls",
 *       @SWG\Items(ref="#/definitions/Ball"),
 *       example="[1,2,3]",
 *     ),
 *     @SWG\Property(
 *       property="line_extra_balls",
 *       type="array",
 *       description="Line extra balls",
 *       @SWG\Items(ref="#/definitions/Ball"),
 *       example="[1,2,3]",
 *     ),
 *     @SWG\Property(
 *       property="match_balls",
 *       type="integer",
 *       description="Match balls",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="match_extra_balls",
 *       type="integer",
 *       description="Match extra balls",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="curr_code",
 *       type="string",
 *       description="Currency",
 *       example="USD"
 *     ),
 *     @SWG\Property(
 *       property="winnings",
 *       type="number",
 *       format="float",
 *       description="Winnings",
 *       example="13.53"
 *     ),
 *     @SWG\Property(
 *       property="raffle_number",
 *       type="string",
 *       description="Raffle number",
 *       example="HQM79504"
 *     ),
 *   ),
 */

class TicketTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(Ticket $ticket) {
        return [
            'identifier' => $ticket->tck_id,
            'line_balls' => $ticket->line_balls,
            'line_extra_balls' => $ticket->line_extra_balls,
            'match_balls' => $ticket->match_balls,
            'match_extra_balls' => $ticket->match_extra_balls,
            'curr_code' => $ticket->curr_code,
            //'winnings' => $ticket->tck_prize_usr,
            'winnings' => round((float)$ticket->tck_prize_usr,2),
            'raffle_number' => isset($ticket->raffle) ? $ticket->raffle : "",
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
