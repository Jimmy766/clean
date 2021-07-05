<?php

namespace App\Core\Users\Transforms;

use App\Core\Rapi\Models\ProductType;
use App\Core\Syndicates\Models\SyndicateRafflePrize;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class UserWinningsSyndicateRaffleListTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(SyndicateRafflePrize $syndicateRafflePrize) {
        return [
            'identifier' => $syndicateRafflePrize->id,
            'product_type_identifier' => 3,
            'product_identifier'=> $syndicateRafflePrize->syndicate_raffle_subscriptions->rsyndicate_id,
            'product_name'=> $syndicateRafflePrize->syndicate_raffle_subscriptions->raffle_syndicate->printable_name,
            'region'=> null,
            'draw_date'=> Carbon::parse($syndicateRafflePrize->raffle_ticket->raffle_draw->rff_playdate)->format('Y-m-d'),
            'prize' => $syndicateRafflePrize->prize,
            'currency' => $syndicateRafflePrize->syndicate_raffle_subscriptions->currency,
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
