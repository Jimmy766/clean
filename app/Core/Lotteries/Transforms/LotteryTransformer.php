<?php

namespace App\Core\Lotteries\Transforms;

use App\Core\Lotteries\Models\Lottery;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="Lottery",
 *     required={"identifier","name","currency","sunday","monday","tuesday","wednesday","thursday","friday","saturday",
 *     "balls","extra_balls","pick_balls","pick_extra","max_common_balls","max_extra_balls","balls_start","extra_balls_start","max_game_exposure",
 *     },
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="Lottery identifier",
 *       example="3"
 *     ),
 *     @SWG\Property(
 *       property="name",
 *       type="string",
 *       description="Name of lottery",
 *       example="Powerball"
 *     ),
 *     @SWG\Property(
 *       property="currency",
 *       type="string",
 *       description="Currency",
 *       example="USD"
 *     ),
 *     @SWG\Property(
 *       property="sunday",
 *       type="integer",
 *       description="Play sunday",
 *       example="0"
 *     ),
 *     @SWG\Property(
 *       property="monday",
 *       type="integer",
 *       description="Play monday",
 *       example="0"
 *     ),
 *     @SWG\Property(
 *       property="tuesday",
 *       type="integer",
 *       description="Play tuesday",
 *       example="0"
 *     ),
 *     @SWG\Property(
 *       property="wednesday",
 *       type="integer",
 *       description="Play wednesday",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="friday",
 *       type="integer",
 *       description="Play friday",
 *       example="0"
 *     ),
 *     @SWG\Property(
 *       property="saturday",
 *       type="integer",
 *       description="Play saturday",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="balls",
 *       type="integer",
 *       description="Number of balls",
 *       example="5"
 *     ),
 *     @SWG\Property(
 *       property="extra_balls",
 *       type="integer",
 *       description="Number of extra balls",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="refund",
 *       type="integer",
 *       description="Refund value",
 *       example="0"
 *     ),
 *     @SWG\Property(
 *       property="pick_balls",
 *       type="integer",
 *       description="Number of user picks for common balls",
 *       example="3"
 *     ),
 *     @SWG\Property(
 *       property="pick_extra",
 *       type="integer",
 *       description="Number of picks for extra balls",
 *       example="0"
 *     ),
 *     @SWG\Property(
 *       property="max_common_balls",
 *       type="integer",
 *       description="Max number of common balls",
 *       example="9"
 *     ),
 *     @SWG\Property(
 *       property="max_extra_balls",
 *       type="integer",
 *       description="Max number of extra balls",
 *       example="0"
 *     ),
 *     @SWG\Property(
 *       property="extra_ball_name",
 *       type="string",
 *       description="Name extras balls",
 *       example="Powerball"
 *     ),
 *     @SWG\Property(
 *       property="extra_balls_start",
 *       type="integer",
 *       description="Start number of extra balls",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="min_ticket_line",
 *       type="integer",
 *       description="Min ticket line",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="have_raffle",
 *       type="integer",
 *       description="Has rafle (1,0)",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="region",
 *       description="Region of lottery",
 *       type="object",
 *       allOf={
 *         @SWG\Schema(ref="#/definitions/Region"),
 *       }
 *     ),
 *     @SWG\Property(
 *       property="draw_date",
 *       description="Next draw date",
 *       type="string",
 *       format="date-time",
 *       example="2018-01-01 12:00:00",
 *     ),
 *     @SWG\Property(
 *       property="jackpot_change",
 *       type="integer",
 *       description="Value of jackpot change or null",
 *       example="100000"
 *     ),
 *  ),
 */

class LotteryTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */

    public static function transform(Lottery $lottery) {
        $result = [
            'identifier' => (integer)$lottery->lot_id,
            'name' => (string)$lottery->name,
            'currency' => (string)$lottery->currency,
            'sunday' => (integer)$lottery->lot_sun,
            'monday' => (integer)$lottery->lot_mon,
            'tuesday' => (integer)$lottery->lot_tue,
            'wednesday' => (integer)$lottery->lot_wed,
            'thursday' => (integer)$lottery->lot_thu,
            'friday' => (integer)$lottery->lot_fri,
            'saturday' => (integer)$lottery->lot_sat,
            'max_individual_draw' => (integer)$lottery->max_individual_draw,
            'balls' => (integer)$lottery->lot_balls,
            'extra_balls' => (integer)$lottery->lot_extra,
            'refund' => (integer)$lottery->lot_reintegro,
            'pick_balls' => (integer)$lottery->lot_pick_balls,
            'pick_extra' => (integer)$lottery->lot_pick_extra,
            'max_common_balls' => (integer)$lottery->lot_maxNum,
            'max_extra_balls' => (integer)$lottery->lot_extra_maxNum,
            'extra_ball_name' => (string)$lottery->extra_name,
            'extra_balls_start' => (integer)$lottery->lot_extra_startNum,
            'min_ticket_line' => (integer)$lottery->slip_min_lines,
            'jackpot' => $lottery->jackpot,
            'big_lotto' => $lottery->big_lotto,
            'jackpot_in_usd' => $lottery->jackpot_usd,
            'region' => $lottery->region_attributes,
            'draw_date' => $lottery->draw_date,
            'active_draw' => $lottery->active_draw_attributes,
            'jackpot_change' => $lottery->jackpot_change,
            'insure_boosted_jackpot' => $lottery->insure_boosted_jackpot,
            'boosted_jackpot' =>  $lottery->boosted_jackpot_attributes,
            'routing_friendly' => $lottery->routing_friendly_attributes,
        ];
        if ($lottery->lot_id == 25) {
            $result['raffle_jackpot'] = '#LOTTERY_GENERAL_EURO_UK_INFO_RAFFLE#';
        }
        return $result;
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'lot_id',
            'name' => 'name',
            'currency' => 'curr_code',
            'sunday' => 'lot_sun',
            'monday' => 'lot_mon',
            'tuesday' => 'lot_tue',
            'wednesday' => 'lot_wed',
            'thursday' => 'lot_thu',
            'friday' => 'lot_fri',
            'saturday' => 'lot_sat',
            'balls' => 'lot_balls',
            'extra_balls' => 'lot_extra',
            'refund' => 'lot_reintegro',
            'pick_balls' => 'lot_pick_balls',
            'pick_extra' => 'lot_pick_extra',
            'max_common_balls' => 'lot_maxNum',
            'max_extra_balls' => 'lot_extra_maxNum',
            'extra_ball_name' => 'extra_name',
            'extra_balls_start' => 'lot_extra_startNum',
            'min_ticket_line' => 'slip_min_lines',
            'have_rafle' => 'lot_raffle_number',
            'jackpot_in_usd' => 'jackpot',

        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'lot_id' => 'identifier',
            'name' => 'name',
            'curr_code' => 'currency' ,
            'lot_sun' => 'sunday',
            'lot_mon' => 'monday',
            'lot_tue' => 'tuesday',
            'lot_wed' => 'wednesday',
            'lot_thu' => 'thursday',
            'lot_fri' => 'friday',
            'lot_sat' => 'saturday',
            'lot_balls' => 'balls',
            'lot_extra' => 'extra_balls',
            'lot_reintegro' => 'refund',
            'lot_pick_balls' => 'pick_balls',
            'lot_pick_extra' => 'pick_extra',
            'lot_maxNum' => 'max_common_balls',
            'lot_extra_maxNum' => 'max_extra_balls',
            'extra_name' => 'extra_ball_name',
            'lot_extra_startNum' => 'extra_balls_start',
            'slip_min_lines' => 'min_ticket_line',
            'lot_raffle_number' => 'have_rafle',
            'jackpot' => 'jackpot_in_usd',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
