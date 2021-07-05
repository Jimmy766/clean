<?php

namespace App\Core\Users\Transforms;

use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="UserWallet",
 *     @SWG\Property(
 *         property="real_money",
 *         type="number",
 *         format="float",
 *         description="Real Money",
 *         example="50.0"
 *       ),
 *       @SWG\Property(
 *         property="vip_money",
 *         type="number",
 *         format="float",
 *         description="Virtual Money",
 *         example="50.0"
 *       ),
 *       @SWG\Property(
 *         property="game_bonus",
 *         type="number",
 *         format="float",
 *         description="Virtual money from games bonuses ",
 *         example="50.0"
 *       ),
 *       @SWG\Property(
 *         property="total_balance",
 *         type="number",
 *         format="float",
 *         description="Total balance",
 *         example="50.0"
 *       ),
 *       @SWG\Property(
 *         property="user_currency",
 *         type="string",
 *         description="User currency",
 *         example="USD"
 *       ),
 *       @SWG\Property(
 *         property="total_balance_client_currency",
 *         type="number",
 *         format="float",
 *         description="Total balance in client currency",
 *         example="50.0"
 *       ),
 *       @SWG\Property(
 *         property="actual_currency",
 *         type="string",
 *         description="Client currency",
 *         example="USD"
 *       ),
 *       @SWG\Property(
 *         property="vip_points",
 *         type="number",
 *         format="float",
 *         example="50.0",
 *         description="VIP Loyalty Points",
 *       ),
 *       @SWG\Property(
 *         property="level",
 *         type="integer",
 *         description="User level (0 => Red, 1 => Yellow, 2 => Green, 3 => Orange)",     *
 *       ),
 *       @SWG\Property(
 *         property="membership",
 *         type="object",
 *         description="User Membership",
 *         allOf={ @SWG\Schema(ref="#/definitions/MembershipUser")}
 *       ),
 *     )
 *  )
 */
class UserExtraDetailsTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform($user) {
        return [
            'real_money' => round((float)$user->usr_acumulado, 2),
            'vip_money' => round((float)$user->usr_vip_bonus, 2),
            'game_bonus' => round((float)$user->casino_bonus_amount(),2),
            'total_balance' => round((float)$user->total_balance,2),
            'user_currency' => (string)$user->curr_code,
            'total_balance_client_currrency' => round((float)$user->total_balance_client_currency,2),
            'client_currency' => request('country_currency'),
            'vip_points' => $user->usr_points,
            'points_to_cash' => $user->user_point_cash,
            'level' => (string)$user->usr_level,
            'membership' => $user->user_membership,
            'cart_balance' => round((float)$user->usr_vip_bonus + (float)$user->usr_acumulado, 2),
            'game_balance' =>  round((float)$user->usr_acumulado + (float) $user->casino_bonus_amount(),2),
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
