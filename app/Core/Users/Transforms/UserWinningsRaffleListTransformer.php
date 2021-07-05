<?php

namespace App\Core\Users\Transforms;

use App\Core\Rapi\Models\ProductType;
use App\Core\Raffles\Models\RaffleSubscription;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class UserWinningsRaffleListTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(RaffleSubscription $raffleSubscription) {
        $winnigs_prizes = $raffleSubscription->winnigs_prizes;
        return [
            'identifier' => (int) $raffleSubscription->rsub_id,
            'product_type_identifier' => (int) 4,
            'product_identifier' => (int) $winnigs_prizes['rff_id'],
            'product_name' => (string) $raffleSubscription->raffle_name,
            'region' => null,
            'draw_date' => (string) Carbon::parse($winnigs_prizes['draw_date'])->format('Y-m-d'),
            'prize' => (double) $winnigs_prizes['prizes'],
            'currency' => (string) $raffleSubscription->currency,
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
