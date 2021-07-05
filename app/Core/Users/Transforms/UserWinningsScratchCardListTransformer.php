<?php

namespace App\Core\Users\Transforms;


use App\Core\Rapi\Models\ProductType;
use App\Core\ScratchCards\Models\ScratchCardSubscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use League\Fractal\TransformerAbstract;

class UserWinningsScratchCardListTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(ScratchCardSubscription $scratchCardSubscription) {
        return [
            'identifier' => $scratchCardSubscription->scratches_sub_id,
            'product_type_identifier' => 7,
            'product_identifier'=> $scratchCardSubscription->scratch_card->id,
            'product_name' => $scratchCardSubscription->scratch_card->name_tag,
            'region'=> null,
            'draw_date' => Carbon::parse($scratchCardSubscription->draw_date)->format('Y-m-d'),
            'prize' => $scratchCardSubscription->prize,
            'currency' => Auth::user()->curr_code,
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
