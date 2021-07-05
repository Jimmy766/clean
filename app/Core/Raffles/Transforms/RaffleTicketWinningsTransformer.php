<?php

namespace App\Core\Raffles\Transforms;

use App\Core\Raffles\Models\RaffleTicket;
use League\Fractal\TransformerAbstract;

/**
 *   @SWG\Definition(
 *     definition="RaffleTicketWinnings",
 *     @SWG\Property(
 *       property="draw_date",
 *       type="string",
 *       format="date-time",
 *       description="Ticket date",
 *       example="2017-03-01 10:06:38"
 *     ),
 *     @SWG\Property(
 *       property="sign",
 *       type="string",
 *       description="Raffle Ticket Sign",
 *       example="Aquarius",
 *     ),
 *     @SWG\Property(
 *       property="number",
 *       type="integer",
 *       description="Ticket Number",
 *       example="12345",
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
 *       property="url",
 *       type="string",
 *       description="Printed Ticket URL",
 *       example="http://www6.trillonario.com/viewRaffleTicketSecureExtra.php?rtck_id=wnppxjaalj"
 *     )
 *   )
 */

class RaffleTicketWinningsTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(RaffleTicket $raffle_ticket) {

        return [
            //'draw_date' => $raffle_ticket->draw_date,
            'number' => $raffle_ticket->rtck_n1,
            //'sign' => $raffle_ticket->sign,
            'prize' => $raffle_ticket->rtck_prize,
            'curr_code' => $raffle_ticket->currency,
            'ticket_url' => $raffle_ticket->url,
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
