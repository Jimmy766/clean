<?php

namespace App\Core\FreeSpin\Services;

use App\Core\FreeSpin\Models\CasinoFreeSpins;
use App\Core\Base\Services\TranslateTextService;
use App\Core\Base\Services\SendLogConsoleService;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class UserHasBonusService
 * @package App\Services
 */
class UserHasBonusService
{

    /**
     * @param $idUser
     * @param $idBonus
     * @return bool
     */
    public function execute($idUser, $idBonus): bool
    {
        $casinoFreeSpin = CasinoFreeSpins::query()
            ->join('casino_freespins_user as cfu', 'cfu.casino_freespins_id', '=', 'casino_freespins.id')
            ->where('casino_freespins.id', $idBonus)
            ->where('cfu.usr_id', $idUser)
            ->first();

        if ($casinoFreeSpin !== null) {
            $message = TranslateTextService::execute('user_has_bonus');

            $array[ 'message' ]  = $message;
            $array[ 'id_user' ]   = $idUser;
            $array[ 'id_promo_code' ] = $idBonus;

            $sendLogConsoleService = new \App\Core\Base\Services\SendLogConsoleService();
            $sendLogConsoleService->execute(request(), 'promo-code', 'access', 'terminate', $array);
            throw new UnprocessableEntityHttpException(
                $message, null, Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return true;
    }

}
