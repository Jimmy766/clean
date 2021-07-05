<?php

namespace App\Core\Casino\Transforms;

use App\Core\Casino\Models\CasinoGamesTransaction;
use League\Fractal\TransformerAbstract;

/**
 *   @SWG\Definition(
 *     definition="CasinoTransaction",
 *     @SWG\Property(
 *       property="game",
 *       type="string",
 *       description="Name of the Game",
 *       example="BlackJack"
 *     ),
 *     @SWG\Property(
 *       property="date",
 *       type="string",
 *       format="date-time",
 *       description="Date",
 *       example="2018-04-11 16:03:01"
 *     ),
 *     @SWG\Property(
 *       property="transactions",
 *       type="array",
 *       @SWG\Items(
 *         @SWG\Property(
 *           property="type",
 *           type="string",
 *           description="Type",
 *           example="BET"
 *         ),
 *         @SWG\Property(
 *           property="amount",
 *           type="number",
 *           format="float",
 *           description="Amount",
 *           example="1.05"
 *         ),
 *         @SWG\Property(
 *           property="curr_code",
 *           type="string",
 *           description="Currency code",
 *           example="USD"
 *         ),
 *       )
 *     ),
 *   )
 */

class CasinoGamesTransactionTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(CasinoGamesTransaction $casinoGamesTransaction){
        return [
            'roundID'=>(string)$casinoGamesTransaction->rounds_id,
            'game'=>(string)$casinoGamesTransaction->gameId,
            'date'=>$casinoGamesTransaction->reg_date,

            'type'=>(string)$casinoGamesTransaction->type,
            'amount'=>$casinoGamesTransaction->amount,
            'currency'=>$casinoGamesTransaction->curr_code,
            'sign'=>$casinoGamesTransaction->sign,
        ];
    }


    /**
     * @param $index
     * @return mixed|null
     */
    public static function originalAttribute($index) {
        $attributes = [
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    /**
     * @param $index
     * @return mixed|null
     */
    public static function transformedAttribute($index) {
        $attributes = [
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
