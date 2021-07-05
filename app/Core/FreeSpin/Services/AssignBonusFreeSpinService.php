<?php

namespace App\Core\FreeSpin\Services;

use App\Core\FreeSpin\Models\CasinoFreeSpinsUser;
use App\Core\Base\Services\TranslateTextService;
use App\Core\Base\Services\SendLogConsoleService;
use App\Core\Base\Services\LogType;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class AssignBonusFreeSpinService
 * @package App\Services
 */
class AssignBonusFreeSpinService
{

    /**
     * @param $user
     * @param $promotion
     * @return bool
     */
    public function execute( $user, $promotion)
    {
        $errorMessage = TranslateTextService::execute('error_save_bonus_free_spin');
        try {
            DB::beginTransaction();

            $casinoFreeSpinsUser                      = new CasinoFreeSpinsUser();
            $casinoFreeSpinsUser->usr_id              = $user->usr_id;
            $casinoFreeSpinsUser->casino_freespins_id = $promotion->id;
            $casinoFreeSpinsUser->send_date           = Carbon::now();
            $casinoFreeSpinsUser->save();

            DB::commit();

            $message                    = TranslateTextService::execute( 'apply_free_spin' );
            $array[ 'apply_free_spin' ] = $message;
            $array[ 'id_user' ]         = $user->usr_id;
            $array[ 'id_promo_code' ]   = $promotion;

            $sendLogConsoleService = new \App\Core\Base\Services\SendLogConsoleService();
            $sendLogConsoleService->execute( request(), 'promo-code', 'access', 'access', $array );
        }
        catch(Exception $exception) {
            DB::rollBack();
            LogType::error(
                __FILE__,
                __LINE__,
                $errorMessage,
                [
                    'exception' => $exception,
                    'usersId'   => Auth::id(),
                ]
            );
            throw new UnprocessableEntityHttpException(
                $errorMessage, null, Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }


        return true;
    }

}
