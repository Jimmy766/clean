<?php

namespace App\Core\Users\Transforms;

use App\Core\Rapi\Models\ProductType;
use App\Core\Syndicates\Models\SyndicatePrize;
use League\Fractal\TransformerAbstract;


class UserWinningsSyndicateListTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(SyndicatePrize $syndicatePrize) {
        return [
            'identifier' => $syndicatePrize->id,
            'product_type_identifier' => 2,
            'product_identifier' => $syndicatePrize->syndicate_subscription->syndicate_id,
            'product_name' => $syndicatePrize->syndicate_subscription->syndicate->printable_name,
            'region' => $syndicatePrize->syndicate_subscription->lottery['region'],
            'draw_date' => $syndicatePrize->draw_date,
            'prize' => $syndicatePrize->prize,
            'currency' => request()->user()->curr_code,
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
