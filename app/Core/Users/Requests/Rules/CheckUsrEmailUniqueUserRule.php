<?php

namespace App\Core\Users\Requests\Rules;

use App\Core\Users\Models\User;
use Illuminate\Contracts\Validation\Rule;

class CheckUsrEmailUniqueUserRule implements Rule
{
    /**
     * @var array|\Illuminate\Foundation\Application|\Illuminate\Http\Request|string
     */
    private $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function passes($attribute, $value)
    {
        $sys = $this->request->input('client_sys_id');
        $user = User::where('sys_id', $sys)->where('usr_email', $value)->first(['usr_email']);

        if ($user !== null) {
            return false;
        }

        return true;
    }

    public function message()
    {
        return __('The email has already been taken.');
    }
}
