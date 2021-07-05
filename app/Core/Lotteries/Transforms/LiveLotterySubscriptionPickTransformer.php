<?php

    namespace App\Core\Lotteries\Transforms;

    use App\Core\Lotteries\Models\LiveLotterySubscriptionPick;
    use League\Fractal\TransformerAbstract;

    /**
     * @SWG\Definition(
     *     definition="LiveLotterySubscriptionPick",
     *     @SWG\Property(
     *       property="identifier",
     *       type="integer",
     *       description="Identifier ID",
     *       example="123",
     *     ),
     *     @SWG\Property(
     *       property="picks",
     *       type="array",
     *       description="Balls picks",
     *       @SWG\Items(type="integer"),
     *       example="[1,2,3]",
     *     ),
     *  ),
     */

    class LiveLotterySubscriptionPickTransformer extends TransformerAbstract {
        /**
         * A Fractal transformer.
         *
         * @return array
         */
        public static function transform(LiveLotterySubscriptionPick $live_lottery_subscription_pick) {
            return [
                'identifier' => (integer)$live_lottery_subscription_pick->pck_id,
                'picks' => $live_lottery_subscription_pick->picks,
            ];
        }
    }
