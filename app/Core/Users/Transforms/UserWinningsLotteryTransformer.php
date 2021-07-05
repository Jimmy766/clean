<?php

namespace App\Core\Users\Transforms;

use App\Core\Rapi\Models\Ticket;
use League\Fractal\TransformerAbstract;

/**
 *   @SWG\Definition(
 *     definition="UserWinningsLottery",
 *     @SWG\Property(
 *       property="prize_number",
 *       type="integer",
 *       description="Ticket Id",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="lottery_name",
 *       type="integer",
 *       description="Name of the Lottery",
 *       example="EuroMillones"
 *     ),
 *     @SWG\Property(
 *       property="draw_date",
 *       type="string",
 *       format="date-time",
 *       description="Draw Date",
 *       example="2018-02-20",
 *     ),
 *     @SWG\Property(
 *       property="prize",
 *       type="number",
 *       format="float",
 *       description="Prize",
 *       example="10.3"
 *     ),
 *     @SWG\Property(
 *       property="currency",
 *       type="string",
 *       description="Prize Currency",
 *       example="USD"
 *     ),
 *     @SWG\Property(
 *       property="status",
 *       type="string",
 *       description="Status",
 *       example="Credited in your account"
 *     ),
 *     @SWG\Property(
 *       property="ticket",
 *       type="array",
 *       description="User ticket numbers",
 *       @SWG\Items(
 *         @SWG\Property(
 *           property="pick_balls",
 *           type="array",
 *           description="Pick balls",
 *           @SWG\Items(ref="#/definitions/Ball"),
 *           example="[1,2,3]",
 *         ),
 *         @SWG\Property(
 *           property="extra_balls",
 *           type="array",
 *           description="Extra balls",
 *           @SWG\Items(ref="#/definitions/Ball"),
 *           example="[1,2,3]",
 *         ),
 *         @SWG\Property(
 *           property="refund_balls",
 *           type="array",
 *           description="Refund balls",
 *           @SWG\Items(ref="#/definitions/Ball"),
 *           example="[1,2,3]",
 *         ),
 *       )
 *     ),
 *     @SWG\Property(
 *       property="resutls",
 *       type="array",
 *       description="Draw numbers",
 *       @SWG\Items(
 *         @SWG\Property(
 *           property="draw_pick_balls",
 *           type="array",
 *           description="Draw pick balls",
 *           @SWG\Items(ref="#/definitions/Ball"),
 *           example="[1,2,3]",
 *         ),
 *         @SWG\Property(
 *           property="draw_extra_balls",
 *           type="array",
 *           description="Draw extra balls",
 *           @SWG\Items(ref="#/definitions/Ball"),
 *           example="[1,2,3]",
 *         ),
 *         @SWG\Property(
 *           property="draw_refund_balls",
 *           type="array",
 *           description="Draw refund balls",
 *           @SWG\Items(ref="#/definitions/Ball"),
 *           example="[1,2,3]",
 *         ),
 *       )
 *     ),
 *   ),
 */

class UserWinningsLotteryTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(Ticket $ticket) {
        return [
            'prize_number' => $ticket->tck_id,
            'lottery_name' => $ticket->subscription->lottery_name,
            'draw_date' => $ticket->draw->draw_date,
            'prize' => $ticket->tck_prize_usr,
            'currency' => $ticket->curr_code,
            'status' => $ticket->status_tag,
            'ticket' => [
                'pick_balls' => $ticket->line_balls,
                'extra_balls' => $ticket->line_extra_balls,
                'refund_balls' => $ticket->line_refund_balls,
            ],
            'results' => [
                'draw_pick_balls' => $ticket->draw->lot_balls,
                'draw_extra_balls' => $ticket->draw->extra_balls,
                'draw_refund_balls' => $ticket->draw->refund_balls,
            ]
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
