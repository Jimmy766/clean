<?php

namespace App\Core\Users\Transforms;

use App\Core\Syndicates\Models\SyndicatePrize;
use League\Fractal\TransformerAbstract;

class UserWinningsSyndicateTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(SyndicatePrize $syndicatePrize) {
        $user_urrency = request()->user()->curr_code;
        return [
            'prize_number' => $syndicatePrize->id,
            'syndicate_name'=> $syndicatePrize->syndicate_subscription->syndicate->printable_name,
            'draw_date'=> $syndicatePrize->tickets->draw->draw_date,
            'prize' => $syndicatePrize->prize,
            'currency' => $user_urrency,
            'status' => '#WINNINGS_DETAIL_CREDIT#',
            'ticket' => [
                'pick_balls' => $syndicatePrize->tickets->line_balls,
                'extra_balls' => $syndicatePrize->tickets->line_extra_balls,
                'refund_balls' => $syndicatePrize->tickets->line_refund_balls,
            ],
            'results' => [
                'draw_pick_balls' => $syndicatePrize->tickets->draw->lot_balls,
                'draw_extra_balls' => $syndicatePrize->tickets->draw->extra_balls,
                'draw_refund_balls' => $syndicatePrize->tickets->draw->refund_balls,
            ],
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
