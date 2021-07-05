<?php

namespace App\Core\Casino\Transforms;

use App\Core\Casino\Models\CasinoBonusUser;
use League\Fractal\TransformerAbstract;

class CasinoBonusUserTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(CasinoBonusUser $casino_bonus_user) {
        return [
            'identifier' => (integer)$casino_bonus_user->id,
            'initial_amount' => $casino_bonus_user->initial_amount,
            'initial_wager' => $casino_bonus_user->initial_wr,
            'amount' => $casino_bonus_user->amount,
            'wager' => $casino_bonus_user->wr,
            'currency' => $casino_bonus_user->curr_code,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'id',
            'initial_amount' => 'initial_amount',
            'initial_wager' => 'initial_wr',
            'amount' => 'amount',
            'wager' => 'wr',
            'currency' => 'curr_code',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'id' => 'identifier',
            'initial_amount' => 'initial_amount',
            'initial_wr' => 'initial_wager',
            'amount' => 'amount',
            'wr' => 'wager',
            'curr_code' => 'currency',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
