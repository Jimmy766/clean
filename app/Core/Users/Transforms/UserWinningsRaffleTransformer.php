<?php

namespace App\Core\Users\Transforms;

use App\Core\Raffles\Models\RaffleSubscription;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class UserWinningsRaffleTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(RaffleSubscription $raffleSubscription) {
        return [
            'raffle_name'=>$raffleSubscription->raffle_name,
            'draw_date'=> Carbon::parse($raffleSubscription->draw_date)->format('Y-m-d'),
            'prize'=>$raffleSubscription->prizes,
            'currency'=>$raffleSubscription->currency,
            'status'=>'#WINNINGS_DETAIL_CREDIT#',
            'tickets_list'=>$raffleSubscription->winnigns_ticketslist
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
