<?php

namespace App\Core\Users\Services;

use App\Core\Users\Models\User;

class FindUserService
{

    /**
     * Public method to find a user for API use
     *
     * WARNING: This method modifies the app's request
     * by merging new variables to it.
     *
     * @param   string      $username  The user's login identifier
     *                                 (tipically, the email address)
     * @return  \App\Core\Users\Models\User|null              If found, the user; null otherwise
     */
    public function execute($username)
    {
        /* Map expected variables set in middleware's request handling */
        request()->merge([
            "client_id" => request('oauth_client_id'),
            "sys_id" => request('client_sys_id'),
        ]);

        return (new User)->findForPassport($username);
    }
}
