<?php

    namespace App\Core\ScratchCards\Transforms;

    use App\Core\ScratchCards\Models\ScratchCardPrice;
    use League\Fractal\TransformerAbstract;

    /**
     * @SWG\Definition(
     *     definition="ScratchCardPrice",
     *     @SWG\Property(
     *       property="identifier",
     *       type="integer",
     *       format="int32",
     *       description="ID elements identifier",
     *       example="305"
     *     ),
     *     @SWG\Property(
     *       property="rounds",
     *       type="integer",
     *       description="Rounds to play scratch card",
     *       example="1"
     *     ),
     *     @SWG\Property(
     *       property="discount",
     *       type="integer",
     *       description="Percent discount scratch card",
     *       example="1"
     *     ),
     *     @SWG\Property(
     *       property="price",
     *       type="integer",
     *       description="Price of scratch card",
     *       example="1"
     *     ),
     *     @SWG\Property(
     *       property="currency",
     *       type="string",
     *       description="Currency",
     *       example="USD"
     *     ),
     *   ),
     */

    class ScratchCardPriceTransformer extends TransformerAbstract
    {
        /**
         * A Fractal transformer.
         *
         * @return array
         */
        public static function transform(ScratchCardPrice $scratch_price) {
            return [
                'identifier' => (integer)$scratch_price->prc_id,
                'rounds' => $scratch_price->rounds,
                'discount' => $scratch_price->info_discount,
                'price' => round((float)$scratch_price->price,2),
                'currency' => $scratch_price->currency,
            ];
        }

        /**
         * @param $index
         *
         * @return mixed|null
         */
        public static function originalAttribute($index) {
            $attributes = [
                'identifier' => 'prc_id',
                'scratch' => 'scratch_id',
                'rounds' => 'rounds',
                'discount' => 'info_discount',
            ];
            return isset($attributes[ $index ]) ? $attributes[ $index ] : null;
        }

        public static function transformedAttribute($index) {
            $attributes = [
                'prc_id' => 'identifier',
                'scratch_id' => 'scratch',
                'rounds' => 'rounds',
                'info_discount' => 'discount',
            ];
            return isset($attributes[ $index ]) ? $attributes[ $index ] : null;
        }
    }
