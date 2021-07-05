<?php

namespace App\Core\Rapi\Transforms;

use App\Core\Rapi\Models\Draw;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="ResultDraw",
 *     required={"pick_balls","extra_balls"},
 *     @SWG\Property(
 *       property="pick_balls",
 *       description="Results balls",
 *       type="array",
 *       @SWG\Items(type="integer"),
 *       example="[1,2,3]"
 *     ),
 *     @SWG\Property(
 *       property="extra_balls",
 *       description="Results extra balls",
 *       type="array",
 *       @SWG\Items(type="integer"),
 *       example="[2]"
 *     ),
 *  ),
 * @SWG\Definition(
 *     definition="Draw",
 *     required={"identifier","external_id","date","time"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="ID Draw identifier",
 *       example="1234"
 *     ),
 *     @SWG\Property(
 *       property="lottery_identifier",
 *       type="integer",
 *       description="ID external identifier",
 *       example="3"
 *     ),
 *     @SWG\Property(
 *       property="lottery_name",
 *       type="string",
 *       description="Lottery name",
 *       example="Powerball"
 *     ),
 *     @SWG\Property(
 *       property="date",
 *       type="string",
 *       format="date",
 *       description="Draw date",
 *       example="2018-06-11"
 *     ),
 *     @SWG\Property(
 *       property="time",
 *       type="string",
 *       description="Draw time",
 *       example="23:30:15"
 *     ),
 *     @SWG\Property(
 *       property="jackpot",
 *       description="Jackpot",
 *       type="integer",
 *       example="1523343615"
 *     ),
 *     @SWG\Property(
 *       property="jackpot_cash",
 *       description="Jackpot cash",
 *       type="integer",
 *       example="1523343615"
 *     ),
 *     @SWG\Property(
 *       property="results",
 *       description="Last draw results object or -1",
 *       type="object",
 *       allOf={
 *         @SWG\Schema(ref="#/definitions/ResultDraw"),
 *         @SWG\Schema(
 *           @SWG\Property(
 *             property="refund_balls",
 *             description="Results refund balls",
 *             type="array",
 *             @SWG\Items(type="integer"),
 *             example="[3]"
 *           ),
 *         ),
 *       }
 *     ),
 *     @SWG\Property(
 *       property="raffle",
 *       description="Rafles",
 *       type="array",
 *       @SWG\Items(ref="#/definitions/ResultRaffle"),
 *     ),
 *  ),
 */

class DrawTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */

    public static function transform(Draw $draw) {
        return [
            'identifier' => (integer)$draw->draw_id,
            'lottery_identifier' => (integer)$draw->lot_id,
            'lottery_name' => $draw->lottery_name,
            'date' => $draw->draw_date,
            'time' => $draw->draw_time,
            'jackpot' => (integer)$draw->draw_jackpot,
            'jackpot_cash' => (integer)$draw->draw_jackpot_cash,
            'jackpot_change' => $draw->jackpot_change,
            'results' => $draw->has_results() ? [
                'pick_balls' => $draw->lot_balls,
                'extra_balls' => $draw->extra_balls,
                'refund_balls' => $draw->refund_balls,
            ] : -1,
            'raffle_results' => $draw->raffles_attributes,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'draw_id',
            'date' => 'draw_date',
            'time' => 'draw_time',
            'jackpot' => 'draw_jackpot',
            'jackpot_cash' => 'draw_jackpot_cash',
            'ball1' => 'draw_ball1',
            'ball2' => 'draw_ball2',
            'ball3' => 'draw_ball3',
            'ball4' => 'draw_ball4',
            'ball5' => 'draw_ball5',
            'ball6' => 'draw_ball6',
            'ball7' => 'draw_ball7',
            'ball8' => 'draw_ball8',
            'ball9' => 'draw_ball9',
            'ball10' => 'draw_ball10',
            'ball11' => 'draw_ball11',
            'ball12' => 'draw_ball12',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'draw_id' => 'identifier',
            'draw_date' => 'date',
            'draw_time' => 'time',
            'jackpot' => 'draw_jackpot',
            'jackpot_cash' => 'draw_jackpot_cash',
            'ball1' => 'draw_ball1',
            'ball2' => 'draw_ball2',
            'ball3' => 'draw_ball3',
            'ball4' => 'draw_ball4',
            'ball5' => 'draw_ball5',
            'ball6' => 'draw_ball6',
            'ball7' => 'draw_ball7',
            'ball8' => 'draw_ball8',
            'ball9' => 'draw_ball9',
            'ball10' => 'draw_ball10',
            'ball11' => 'draw_ball11',
            'ball12' => 'draw_ball12',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
