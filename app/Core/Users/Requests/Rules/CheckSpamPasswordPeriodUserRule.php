<?php

namespace App\Core\Users\Requests\Rules;

use App\Core\Base\Services\TranslateTextService;
use App\Core\Users\Models\User;
use Auth;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Cache;

/**
 * Class CheckSpamPasswordPeriodUserRule
 * @package App\Http\Requests\Rules
 */
class CheckSpamPasswordPeriodUserRule implements Rule
{
    private $request;
    /**
     * @var string
     */
    private $messageResponse;

    public function __construct()
    {
        $this->request         = request();
        $this->messageResponse = "";
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $ip       = $this->request->input('user_ip');
        $password = $this->request->input('usr_password');
        $value    = $this->request->input('current_password');
        if(is_null($value) && is_null($password)){
           return true;
        }
        $idUser = Auth::id();

        $key  = "{$ip}-{$idUser}";
        $time = config('constants.cache_5');

        $count = Cache::get($key);

        if ($count === null) {
            $count = 0;
        }

        if ($count >= 3) {
            $this->messageResponse = TranslateTextService::execute('has_exceeded_the_number_of_attempts_please_wait_5_minutes');
            return false;
        }

        $user = User::query()->where('usr_id', $idUser)->first(['usr_password']);

        if($user === null){
            $this->messageResponse = TranslateTextService::execute('user_does_not_exist');
            return false;
        }

        if($user->usr_password !== $value){
            $this->messageResponse = TranslateTextService::execute('the_current_password_is_not_correct');
            if ($count === 0) {
                Cache::put($key, 1, $time);
            }

            ++$count;

            Cache::put($key, $count, $time);
            return false;
        }

        return true;
    }

    public function message()
    {
        return $this->messageResponse;
    }
}
