<?php

namespace App\Core\FreeSpin\Services;

use App\Core\Base\Classes\ModelConst;
use App\Core\Base\Services\TranslateTextService;
use App\Core\FreeSpin\Services\CountBonusFreeSpinUseService;
use App\Core\Base\Services\SendLogConsoleService;
use App\Core\FreeSpin\Services\UserHasBonusService;
use App\Core\Users\Services\UserRegisterBeforeService;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class CanUseBonusService
 * @package App\Services
 */
class CanUseBonusService
{

    /**
     * @var CountBonusFreeSpinUseService
     */
    private $countBonusFreeSpinUseService;
    /**
     * @var UserRegisterBeforeService
     */
    private $userRegisterBeforeService;
    /**
     * @var UserHasBonusService
     */
    private $userHasBonusService;

    public function __construct(
        CountBonusFreeSpinUseService $countBonusFreeSpinUseService,
        UserRegisterBeforeService $userRegisterBeforeService,
        UserHasBonusService $userHasBonusService
    ) {
        $this->countBonusFreeSpinUseService = $countBonusFreeSpinUseService;
        $this->userRegisterBeforeService    = $userRegisterBeforeService;
        $this->userHasBonusService          = $userHasBonusService;
    }

    /**
     * @param $idUser
     * @param $promotion
     * @return bool
     */
    public function execute( $idUser, $promotion): bool
    {
        if ($promotion === null) {
            return false;
        }

        $countFreeSpinUse = $this->countBonusFreeSpinUseService->execute($promotion->id);

        if ($countFreeSpinUse >= $promotion->total_uses) {
            $message = TranslateTextService::execute('max_promotion_code_use');

            $array[ 'message' ]       = $message;
            $array[ 'total_use' ]     = $countFreeSpinUse;
            $array[ 'id_user' ]       = $idUser;
            $array[ 'id_promo_code' ] = $promotion->id;

            $sendLogConsoleService = new SendLogConsoleService();
            $sendLogConsoleService->execute(request(), 'promo-code', 'access', 'terminate', $array);
            throw new UnprocessableEntityHttpException(
                $message, null, Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        if ($promotion->freespins_type == ModelConst::LOGIN_FREE_SPIN) {
            $this->userRegisterBeforeService->execute(
                $idUser,
                $promotion
            );
        }

        $this->userHasBonusService->execute(
            $idUser,
            $promotion->id
        );

        return true;
    }
}
