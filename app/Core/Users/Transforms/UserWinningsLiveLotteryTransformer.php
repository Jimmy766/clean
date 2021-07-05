<?php

namespace App\Core\Users\Transforms;


use App\Core\Lotteries\Models\LiveLotterySubscription;
use League\Fractal\TransformerAbstract;


class UserWinningsLiveLotteryTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(LiveLotterySubscription $live_lottery_subscription) {
        return [
            'prize_number' => (integer)$live_lottery_subscription->ticket->tck_id,
            'lottery_name' => $live_lottery_subscription->lottery ? $live_lottery_subscription->lottery->lot_name : '',
            'draw_date'=>$live_lottery_subscription->draw->draw_date_display,
            'draw_number'=> (int) $live_lottery_subscription->draw->draw_external_id,
            'prize'=>$live_lottery_subscription->ticket->tck_prize_usr,
            'currency'=>$live_lottery_subscription->ticket->curr_code,
            'status' => $live_lottery_subscription->ticket->status_tag,
            'ticket' => [
                'pick_balls' => $live_lottery_subscription->ticket->balls,
                'extra_balls' => $live_lottery_subscription->ticket->extra_balls,
            ],
            'results' => [
                'draw_pick_balls' => $live_lottery_subscription->draw->lot_balls,
                'draw_extra_balls' => $live_lottery_subscription->draw->extra_balls,
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
