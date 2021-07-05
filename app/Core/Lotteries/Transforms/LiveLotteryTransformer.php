<?php

namespace App\Core\Lotteries\Transforms;

use App\Core\Lotteries\Models\LiveLottery;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="LiveLottery",
 *     required={"identifier","name","fancy_name","currency","balls","extra_balls","pick_balls","pick_extra","max_common_balls","max_extra_balls","balls_start","extra_balls_start","max_game_exposure","bets","modifiers","hidden_modifiers"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="ID Draw identifier",
 *       example="1234"
 *     ),
 *     @SWG\Property(
 *       property="name",
 *       type="string",
 *       description="Name of live lottery",
 *       example="Quick 3"
 *     ),
 *     @SWG\Property(
 *       property="currency",
 *       type="string",
 *       description="Currency",
 *       example="USD"
 *     ),
 *     @SWG\Property(
 *       property="balls",
 *       type="integer",
 *       description="Number of balls",
 *       example="3"
 *     ),
 *     @SWG\Property(
 *       property="extra_balls",
 *       type="integer",
 *       description="Number of extra balls",
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
 *       property="balls_start",
 *       type="integer",
 *       description="Start number of common balls",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="extra_balls_start",
 *       type="integer",
 *       description="Start number of extra balls",
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
 *       property="max_game_exposure",
 *       type="integer",
 *       description="Max value of bets",
 *       example="100.0"
 *     ),
 *     @SWG\Property(
 *       property="streaming_url",
 *       type="array",
 *       description="Video streaming url",
 *       @SWG\Items(
 *            @SWG\Property(property="defaults", type="string",example="https://us.dvr1.amberbox.eu/bs-gen-q3/ntsc/Manifest.mpd"),
 *            @SWG\Property(property="ios", type="string",example="https://us.dvr1.amberbox.eu/bs-gen-q3/ntsc/index.m3u8")
 *       )
 *     ),
 *     @SWG\Property(
 *       property="bets",
 *       description="Bets of lottery",
 *       type="object",
 *       allOf={
 *         @SWG\Schema(ref="#/definitions/Bet"),
 *       }     *
 *     ),
 *     @SWG\Property(
 *       property="modifiers",
 *       description="Results extra balls",
 *       type="array",
 *       @SWG\Items(ref="#/definitions/LotteryModifier"),
 *     ),
 *  ),
 */

class LiveLotteryTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(LiveLottery $live_lottery) {
        return [
            'identifier' => (integer)$live_lottery->lot_id,
            'name' => (string)$live_lottery->lot_name,
            'currency' => request('country_currency'),
            'balls' => (integer)$live_lottery->lot_balls,
            'extra_balls' => (integer)$live_lottery->lot_extra,
            'pick_balls' => (integer)$live_lottery->lot_pick_balls,
            'pick_extra' => (integer)$live_lottery->lot_pick_extra,
            'balls_start' => (integer)$live_lottery->start_num,
            'extra_balls_start' => (integer)$live_lottery->lot_extra_startNum,
            'max_common_balls' => (integer)$live_lottery->lot_maxNum,
            'max_extra_balls' => (integer)$live_lottery->lot_extra_maxNum,
            'streaming_url' => $live_lottery->streaming_url,
            'bets' => $live_lottery->bet,
            'bet_types' => $live_lottery->modifiers_list,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'lot_id',
            'name' => 'lot_name_en',
            'currency' => 'curr_code',
            'balls' => 'lot_balls',
            'extra_balls' => 'lot_extra',
            'pick_balls' => 'lot_pick_balls',
            'pick_extra' => 'lot_pick_extra',
            'max_common_balls' => 'lot_maxNum',
            'max_extra_balls' => 'lot_extra_maxNum',
            'extra_balls_start' => 'lot_extra_startNum',

        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'lot_id' => 'identifier',
            'lot_name_en' => 'name',
            'curr_code' => 'currency' ,
            'lot_balls' => 'balls',
            'lot_extra' => 'extra_balls',
            'lot_pick_balls' => 'pick_balls',
            'lot_pick_extra' => 'pick_extra',
            'lot_maxNum' => 'max_common_balls',
            'lot_extra_maxNum' => 'max_extra_balls',
            'lot_extra_startNum' => 'extra_balls_start',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
