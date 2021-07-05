<?php

namespace App\Core\Users\Requests\Rules;

use App\Core\Users\Models\User;
use Illuminate\Contracts\Validation\Rule;

class CheckDuplicateUserRule implements Rule
{
    private $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function passes($attribute, $value)
    {
        $usrName     = $this->request->input('usr_name');
        $usrLastName = $this->request->input('usr_lastname');
        $usrPhone    = $this->request->input('usr_phone');
        $countryId   = $this->request->input('country_id');

        $user = User::where('usr_name', $usrName)
            ->where('usr_lastname', $usrLastName)
            ->where('usr_phone', $usrPhone)
            ->where('country_id', $countryId)
            ->first(['usr_email']);

        if ($user !== null) {
            return false;
        }

        return true;
    }

    public function message()
    {
        return __('possible_fraud_duplicate_user');
    }
}
