<?php

namespace App\Core\FreeSpin\Services;

use App\Core\Base\Classes\ModelConst;
use App\Core\Base\Services\TranslateTextService;
use App\Core\FreeSpin\Services\AssignBonusFreeSpinService;
use App\Core\FreeSpin\Services\CheckFraudBonusFreeSpinService;
use App\Core\FreeSpin\Services\CanUseBonusService;
use App\Core\FreeSpin\Services\GetBonusInfoService;
use App\Core\Base\Services\GetInfoFromExceptionService;
use App\Core\Base\Services\SendLogConsoleService;
use App\Core\Users\Services\SetUSerOneTimeRejectedService;
use App\Core\Base\Traits\ErrorNotificationTrait;
use Exception;
use Illuminate\Http\Response;

/**
 * Class ApplyBonusFreeSpinService
 * @package App\Services
 */
class ApplyBonusFreeSpinService
{

    use ErrorNotificationTrait;

    /**
     * @var CanUseBonusService
     */
    private $canUseBonusService;
    /**
     * @var \App\Core\FreeSpins\Services\GetBonusInfoService
     */
    private $getBonusInfoService;

    /**
     * @var CheckFraudBonusFreeSpinService
     */
    private $checkFraudBonusFreeSpinService;

    /**
     * @var AssignBonusFreeSpinService
     */
    private $assignBonusFreeSpinService;

    /**
     * @var \App\Core\Users\Services\SetUSerOneTimeRejectedService
     */
    private $setUSerOneTimeRejectedService;

    public function __construct(
        CanUseBonusService $canUseBonusService,
        \App\Core\FreeSpin\Services\GetBonusInfoService $getBonusInfoService,
        CheckFraudBonusFreeSpinService $checkFraudBonusFreeSpinService,
        AssignBonusFreeSpinService $assignBonusFreeSpinService,
        SetUSerOneTimeRejectedService $setUSerOneTimeRejectedService
    ) {

        $this->canUseBonusService             = $canUseBonusService;
        $this->getBonusInfoService            = $getBonusInfoService;
        $this->checkFraudBonusFreeSpinService = $checkFraudBonusFreeSpinService;
        $this->assignBonusFreeSpinService     = $assignBonusFreeSpinService;
        $this->setUSerOneTimeRejectedService  = $setUSerOneTimeRejectedService;
    }

    /**
     * @param $request
     * @param $user
     * @return array|false
     */
    public function execute( $request, $user )
    {

        $pcbr      = $request->pcbr;
        $pcbl      = $request->pcbl;
        $promoCode = $pcbr === null ? null : $pcbr;
        $promoCode = $pcbl === null ? $promoCode : $pcbl;
        $typePromo = null;

        if ( $promoCode === null ) {
            return [];
        }

        if ( $pcbr !== null ) {
            $typePromo = ModelConst::REGISTER_FREE_SPIN;
        }

        if ( $pcbl !== null ) {
            $typePromo = ModelConst::LOGIN_FREE_SPIN;
        }

        $idUser                     = $user->usr_id;
        $array[ 'id_user' ]         = $idUser;
        $array[ 'ip_user' ]         = $request->user_ip;
        $array[ 'promo_code' ]      = $promoCode;
        $array[ 'type_promo_code' ] = $typePromo;
        $array[ 'tag' ]             = 'apply_free_spin';

        $array[ 'code_response_promo_code' ] = Response::HTTP_OK;

        //dont return errors to response, only send log in function call method send log
        try {
            $promotion = $this->getBonusInfoService->execute( $user, $promoCode, $typePromo );
            $this->canUseBonusService->execute( $idUser, $promotion );
            $this->checkFraudBonusFreeSpinService->execute( $request, $user, $promotion );
            $this->assignBonusFreeSpinService->execute( $user, $promotion );

            $array[ 'message' ] = TranslateTextService::execute( 'apply_free_spin' );

            return $array;
        }
        catch ( Exception $exception ) {

            $code                                = $exception->getCode();
            $message                             = $exception->getMessage();
            $array[ 'tag' ]                      = $message;
            $array[ 'message' ]                  = TranslateTextService::execute( $message );
            $array[ 'code_response_promo_code' ] = $code;

            $sendLogConsoleService = new SendLogConsoleService();
            $sendLogConsoleService->execute( request(), 'promo-code', 'access', 'access', $array );

            $infoEndpoint                    = \App\Core\Base\Services\GetInfoFromExceptionService::execute($request, $exception, $array );
            $infoEndpoint[ 'message_error' ] = "PROMO CODE " . $infoEndpoint[ 'message_error' ];
            $nameException                   = "{$message}-{$idUser}";
            $this->sendErrorNotification( $infoEndpoint, $nameException );

            if ( $code === Response::HTTP_BAD_REQUEST ) {
                $this->setUSerOneTimeRejectedService->execute( $user, $request->user_ip );
            }

            return $array;
        }
    }

}
