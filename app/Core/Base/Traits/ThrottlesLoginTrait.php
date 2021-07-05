<?php

namespace App\Core\Base\Traits;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

/**
 * Trait ThrottlesLoginTrait
 *
 * @package App\Traits\Auth
 */
trait ThrottlesLoginTrait
{
    /**
     * Determine if the user has too many failed login attempts.
     *
     * @param Request $request
     * @return bool
     */
    protected function hasTooManyLoginAttempts(Request $request): bool
    {
        return $this->limiter()->tooManyAttempts(
            $this->throttleKey($request), $this->maxAttempts()
        );
    }

    /**
     * Increment the login attempts for the user.
     *
     * @param Request $request
     */
    protected function incrementLoginAttempts(Request $request): void
    {
        $this->limiter()->hit(
            $this->throttleKey($request), $this->decayMinutes()
        );
    }

    /**
     * Redirect the user after determining they are locked out.
     *
     * @param Request $request
     * @throws \Exception
     */
    protected function sendLockoutResponse(Request $request): void
    {
        $timeSeconds = $this->limiter()->availableIn(
            $this->throttleKey($request)
        );

        [$hours, $minutes, $seconds, $messageEs, $messageEn] = $this->converterTimeSeconds($timeSeconds);

        $messageError = Lang::get( 'auth.throttle',
            [
                'hours'      => $hours,
                'minutes'    => $minutes,
                'seconds'    => $seconds,
                'message-es' => $messageEs,
                'message-en' => $messageEn,
            ] );
        $message = [
            'error' => [$messageError],
            'info' => 'demasiados intentos',
        ];
        throw new \Exception( json_encode( $message ), 429 );
    }

    /**
     * Clear the login locks for the given user credentials.
     *
     * @param Request $request
     */
    protected function clearLoginAttempts(Request $request): void
    {
        $this->limiter()->clear($this->throttleKey($request));
    }

    /**
     * Fire an event when a lockout occurs.
     *
     * @param Request $request
     */
    protected function fireLockoutEvent(Request $request): void
    {
        event(new Lockout($request));
    }

    /**
     * Get the throttle key for the given request.
     *
     * @param Request $request
     * @return string
     */
    protected function throttleKey(Request $request): string
    {
        return Str::lower($request->input($this->username())) . '|' . $request->ip();
    }

    /**
     * Get the rate limiter instance.
     *
     * @return \Illuminate\Foundation\Application|mixed
     */
    protected function limiter()
    {
        return app(RateLimiter::class);
    }

    /**
     * Get the maximum number of attempts to allow.
     *
     * @return int
     */
    public function maxAttempts(): int
    {
        return property_exists($this, 'maxAttempts') ? $this->maxAttempts : 5;
    }

    /**
     * Get the number of minutes to throttle for.
     *
     * @return int
     */
    public function decayMinutes(): int
    {
        return property_exists($this, 'decayMinutes') ? $this->decayMinutes : 1;
    }

    /**
     * @param $seconds
     * @return array
     */
    private function converterTimeSeconds($seconds): array
    {
        $hours = (int)floor($seconds / 3600);
        $minutes = (int)floor(($seconds - ($hours * 3600)) / 60);
        $seconds = ($seconds - ($hours * 3600) - ($minutes * 60));

        /** Valida si es plural los minutos y segundos **/
        $includePluralSeconds = (($seconds === 1) ? '' : 's');
        $includePluralMinutes = (($minutes === 0) ? '' : 's');

        /** Cambiar el formato agregando un cero adelante **/
        $minutesFormat = str_pad($minutes, 2, '0', STR_PAD_LEFT);
        $secondsFormat = str_pad($seconds, 2, '0', STR_PAD_LEFT);

        if ($minutes === 0) {
            $messageEs = '00:' . ($secondsFormat . ' segundo') . $includePluralSeconds;
            $messageEn = '00:' . $secondsFormat . ' second' . $includePluralSeconds;
        } else {
            $messageEs = $minutesFormat . ':' . $secondsFormat . ' minuto' . $includePluralMinutes;
            $messageEn = $minutesFormat . ':' . $secondsFormat . ' minute' . $includePluralMinutes;
        }

        return [$hours, $minutes, $seconds, $messageEs, $messageEn];
    }
}
