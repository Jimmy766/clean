<?php

namespace App\Core\Messages\Transforms;

use App\Core\Messages\Models\PromotionExtraMessage;
use League\Fractal\TransformerAbstract;

class PromotionExtraMessageTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    /**
     * @SWG\Definition(
     *     definition="PromotionExtraMessage",
     *     @SWG\Property(
     *       property="language",
     *       type="string",
     *       description="Extra message language",
     *       example="en"
     *     ),
     *     @SWG\Property(
     *       property="text",
     *       type="string",
     *       description="Extra message text",
     *       example=""
     *     ),
     *  ),
     */
    public static function transform(PromotionExtraMessage $promotion_extra_message) {
        return [
            'language' => (string)$promotion_extra_message->lang,
            'text' => (string)$promotion_extra_message->text,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'promotion_id',
            'language' => 'lang',
            'text' => 'text',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'promotion_id' => 'identifier',
            'lang' => 'language',
            'text' => 'text',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
