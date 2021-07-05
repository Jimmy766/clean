<?php

namespace App\Core\Rapi\Transforms;

use App\Core\Rapi\Models\Promotion;
use League\Fractal\TransformerAbstract;

class PromotionListTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */


/**
 * @SWG\Definition(
 *     definition="Promotion",
 *     required={"identifier"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="Promotion ID Identifier",
 *       example="744441"
 *     ),
 *     @SWG\Property(
 *       property="name_description",
 *       type="string",
 *       description="Description",
 *       example="Deal 5% California SuperLotto"
 *     ),
 *     @SWG\Property(
 *       property="code",
 *       type="string",
 *       description="Promo code",
 *       example="FAFF74"
 *     ),
 *     @SWG\Property(
 *       property="discount_type",
 *       type="integer",
 *       description="Discount type",
 *       example="2"
 *     ),
 *     @SWG\Property(
 *       property="lot_ids",
 *       type="array",
 *       @SWG\Items(ref="#/definitions/Promotion"),
 *       description="Lotteries Ids associated",
 *       example=""
 *     ),
 *     @SWG\Property(
 *       property="expiration_date",
 *       type="string",
 *       format="date-time",
 *       description="Expiration date",
 *       example="2018-09-09 00:00:00"
 *     ),
 *  ),
 */

    public static function transform(Promotion $promotion) {
        return [
            'identifier' => (integer)$promotion->promotion_id,
            'name_description' => (string)$promotion->name,
            'code' => (string)$promotion->code,
            'discount_type' => (integer)$promotion->discount_type,
            'lot_ids'   => $promotion->lotteries,
            'expiration_date'   => (string)$promotion->expiration_date,
        ];
    }


    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'promotion_id',
            'name' => 'name',
            'description' => 'promo_description',
            'code' => 'code',
            'discount_type' => 'discount_type',
            'user_type' => 'user_type',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'promotion_id' => 'identifier',
            'name' => 'name',
            'promo_description' => 'description',
            'code' => 'code',
            'discount_type' => 'discount_type',
            'user_type' => 'user_type',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
