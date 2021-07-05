<?php

namespace App\Core\Users\Services;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class GetBalanceUserService
{

    /**
     * @param $game
     * @return array
     */
    public function execute($game = null): array
    {
        if (Auth::user() === null) {
            $message = __('user not login');
            throw new UnprocessableEntityHttpException($message, null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $bonusBalance = $this->userActiveGameBonus($game);
        $balanceUser  = Auth::user()->usr_acumulado;

        $balanceWithBonusGame = $balanceUser + $bonusBalance;

        return [
            'balance_user'            => $balanceUser,
            'balance_user_bonus_game' => $balanceWithBonusGame,
        ];
    }

    private function userActiveGameBonus($game = null)
    {
        if ($game === null) {
            return 0;
        }
        $valuesBonusUser = Auth::user()
            ->casino_bonus_user()
            ->whereHas(
                'bonus_category.casino_category.games_category',
                function ($query) use ($game) {
                    $query->where('casino_games_id', '=', $game->id);
                }
            )
            ->first();

        return $valuesBonusUser === null ?  0 : $valuesBonusUser->amount_converted;
    }

}
