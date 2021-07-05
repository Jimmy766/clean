<?php

namespace App\Core\Users\Services;

use App\Core\Base\Services\TranslateTextService;
use App\Core\Base\Services\LogType;
use App\Core\Users\Models\UserOneTimeRejected;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class AssignBonusFreeSpinService
 * @package App\Services
 */
class SetUSerOneTimeRejectedService
{

    /**
     * @param $user
     * @param $ipUser
     * @return bool
     */
    public function execute( $user, $ipUser ): bool
    {

        $errorMessage = TranslateTextService::execute( 'error_save_user_one_time_rejected' );
        try {

            DB::beginTransaction();

            $userOneTimeRejected               = new UserOneTimeRejected();
            $userOneTimeRejected->usr_id       = $user->usr_id;
            $userOneTimeRejected->usr_name     = $user->usr_name;
            $userOneTimeRejected->usr_lastname = $user->usr_lastname;
            $userOneTimeRejected->usr_email    = $user->usr_email;
            $userOneTimeRejected->usr_email    = $user->usr_email;
            $userOneTimeRejected->ip           = $ipUser;
            $userOneTimeRejected->save();

            DB::commit();

            return true;

        }
        catch ( Exception $exception ) {
            DB::rollBack();
            LogType::error( __FILE__, __LINE__, $errorMessage, [
                'exception' => $exception,
                'usersId'   => Auth::id(),
            ] );
        }

        return false;
    }

}
