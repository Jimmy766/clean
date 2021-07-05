<?php

namespace App\Core\Rapi\Transforms;

use App\Core\Rapi\Models\PayoutMethod;
use League\Fractal\TransformerAbstract;

class PayoutMethodTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(PayoutMethod $payout) {
        return [
            'identifier' => $payout->payout_id,
            'name' => $payout->name,
            'tag_name' => $payout->tag_name,
            'payway_id' => $payout->pay_id,
            'country_id' => $payout->country_id,
        ];
    }

    /**
     * @param $index
     * @return mixed|null
     */
    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'payout_id',
            'name' => 'name',
            'tag_name' => 'tag_name',
            'payway_id' => 'pay_id',
            'country_id' => 'country_id',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'payout_id' => 'identifier',
            'name' => 'name',
            'tag_name' => 'tag_name',
            'pay_id' => 'payway_id',
            'country_id' => 'country_id',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
