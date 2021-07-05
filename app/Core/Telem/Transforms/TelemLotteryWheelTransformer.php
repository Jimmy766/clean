<?php


namespace App\Core\Telem\Transforms;


use App\Core\Lotteries\Models\Lottery;
use League\Fractal\TransformerAbstract;

class TelemLotteryWheelTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(Lottery $lottery) {
        return [
            "identifier" => $lottery->lot_id,
            "name"=> $lottery->lot_name_en,
            "currency"=> $lottery->currency,
            "balls" => $lottery->lot_balls,
            "extra_balls" => $lottery->lot_extra,
            "pick_balls" => $lottery->lot_pick_balls,
            "max_draws_playing" => $lottery->lot_max_draws_playing,
            "pick_extra" => $lottery->lot_pick_extra,
            "auto_pick_reintegro" => $lottery->lot_auto_pick_reintegro,
            'extra_balls_start' => (integer)$lottery->lot_extra_startNum,
            "pick_reintegro" => $lottery->lot_pick_reintegro,
            "max_common_balls" => $lottery->lot_maxNum,
            "max_extra_balls" => $lottery->lot_extra_maxNum,
            "min_ticket_line" => $lottery->slip_min_lines,
            "draws_to_show" => $lottery->draws_to_show
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'lot_id',
            'name' => 'lot_name_en',
            "balls" => 'lot_balls',
            "extra_balls" => 'lot_extra',
            "pick_balls" => 'lot_pick_balls',
            "max_draws_playing" => 'lot_max_draws_playing',
            "pick_extra" => 'lot_pick_extra',
            "auto_pick_reintegro" => 'lot_auto_pick_reintegro',
            "pick_reintegro" => 'lot_pick_reintegro',
            "max_common_balls" => 'lot_maxNum',
            "max_extra_balls" => 'lot_extra_maxNum',
            "min_ticket_line" => 'slip_min_lines'
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'id' => 'identifier',
            'lot_name_en' => 'name',
            "lot_balls" => 'balls',
            "lot_extra" => 'extra_balls',
            "lot_pick_balls" => 'pick_balls',
            "lot_max_draws_playing" => 'max_draws_playing',
            "lot_pick_extra" => 'pick_extra',
            "lot_auto_pick_reintegro" => 'auto_pick_reintegro',
            "lot_pick_reintegro" => 'pick_reintegro',
            "lot_maxNum" => 'max_common_balls',
            "lot_extra_maxNum" => 'max_extra_balls',
            "slip_min_lines" => 'min_ticket_line'
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

}
