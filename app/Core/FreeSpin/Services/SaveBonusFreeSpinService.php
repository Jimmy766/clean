<?php

namespace App\Core\FreeSpin\Services;

use App\Core\FreeSpin\Models\CasinoFreeSpinsUser;
use App\Core\Base\Services\LogType;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class RegisterBonusFreeSpinService
 * @package App\Services
 */
class SaveBonusFreeSpinService
{

    public function execute($idUser, $promoCode): bool
    {
        $errorMessage = 'error save casino free spins user';
        try {
            DB::beginTransaction();

            $casinoFreeSpinsUser = new CasinoFreeSpinsUser();

            $casinoFreeSpinsUser->usr_id              = $idUser;
            $casinoFreeSpinsUser->casino_freespins_id = $promoCode;
            DB::commit();
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
            return false;
        }

        return true;
    }

}
