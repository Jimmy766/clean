<?php

namespace App\Core\Users\Transforms;

use App\Core\Syndicates\Models\SyndicateRafflePrize;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class UserWinningsSyndicateRaffleTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(SyndicateRafflePrize $syndicateRafflePrize) {
        return [
            'prize_number' => $syndicateRafflePrize->id,
            'syndicate_raffle_name'=> $syndicateRafflePrize->syndicate_raffle_subscriptions->raffle_syndicate->printable_name,
            'draw_date'=> Carbon::parse($syndicateRafflePrize->raffle_ticket->draw_date)->format('Y-m-d'),
            'prize' => $syndicateRafflePrize->prize,
            'currency' => $syndicateRafflePrize->syndicate_raffle_subscriptions->currency,
            'status' => '#WINNINGS_DETAIL_CREDIT#',
            'tickets_list'=>$syndicateRafflePrize->tickets_list,
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
