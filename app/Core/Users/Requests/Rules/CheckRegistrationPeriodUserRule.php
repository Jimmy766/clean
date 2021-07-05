<?php

namespace App\Core\Users\Requests\Rules;

use App\Core\Users\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Str;

class CheckRegistrationPeriodUserRule implements Rule
{
    private $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function passes($attribute, $value)
    {
        $email = $this->request->input('usr_email');
        if (Str::contains($email, "trillonario.com") === false) {
            $ip   = $this->request->input('user_ip');
            $user = User::where('usr_ip', $ip)
                ->orderByDesc('usr_regdate')
                ->first([ 'usr_regdate' ]);

            if ($user !== null) {
                if ($user->usr_regdate->diffInHours(Carbon::now()) < User::PERIOD_USER) {
                    return false;
                }
            }
        }

        return true;
    }

    public function message()
    {
        return __('possible_ip_fraud');
    }
}
