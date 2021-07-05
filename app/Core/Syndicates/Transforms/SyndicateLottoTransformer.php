<?php

namespace App\Core\Syndicates\Transforms;

use App\Core\Syndicates\Models\SyndicateLotto;
use League\Fractal\TransformerAbstract;

class SyndicateLottoTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(SyndicateLotto $syndicate_lotto) {
        return [
            'identifier' => (integer)$syndicate_lotto->id,
            'tickets_qty' => (integer)$syndicate_lotto->tickets,
            'lottery' => $syndicate_lotto->lottery_attributes,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'id',
            'tickets_qty' => 'tickets',
            'lottery' => 'lottery_attributes',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'id' => 'identifier',
            'tickets' => 'tickets_qty',
            'lottery_attributes' => 'lottery',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
