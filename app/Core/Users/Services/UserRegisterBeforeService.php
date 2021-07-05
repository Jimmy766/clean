<?php

namespace App\Core\Users\Services;

use App\Core\Base\Services\SendLogConsoleService;
use App\Core\Base\Services\TranslateTextService;
use App\Core\Users\Models\User;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class UserRegisterBeforeService
 * @package App\Services
 */
class UserRegisterBeforeService
{

    /**
     * @param $idUser
     * @param $regDate
     * @param $promotion
     * @return bool
     */
    public function execute($idUser, $promotion): bool
    {
        $user = User::query()
            ->where('usr_id', $idUser)
            ->where('usr_regdate', '<', $promotion->user_reg_date_before)
            ->first();

        if ($user === null) {
            $message = TranslateTextService::execute('user_register_before');

            $array[ 'message' ]   = $message;
            $array[ 'id_user' ]   = $idUser;
            $array[ 'usr_regdate' ] = $promotion->user_reg_date_before;
            $array[ 'id_promo_code' ]  = $promotion->id;

            $sendLogConsoleService = new SendLogConsoleService();
            $sendLogConsoleService->execute(request(), 'promo-code', 'access', 'terminate', $array);

            throw new UnprocessableEntityHttpException(
                $message, null, Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return true;
    }

}
