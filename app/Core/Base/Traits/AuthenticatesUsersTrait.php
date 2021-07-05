<?php

namespace App\Core\Base\Traits;

use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trait AuthenticatesUsersTrait
 *
 * @package App\Traits\Auth
 */
trait AuthenticatesUsersTrait
{

    use RedirectsUsersTrait;
    use ThrottlesLoginTrait;

    /**
     * Show the application's login form.
     *
     * @return Factory|View
     */
    public function showLoginForm()
    {

        return view( 'auth.login' );
    }

    /**
     * Handle a login request to the application.
     *
     * @param Request $loginAuthRequest
     * @return \Illuminate\Http\Response|Response|void
     * @throws Exception
     */
    public function login( Request $loginAuthRequest )
    {

        $this->validateLogin( $loginAuthRequest );

        // If the class is using the ThrottlesLoginTrait trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ( $this->hasTooManyLoginAttempts( $loginAuthRequest ) ) {
            $this->fireLockoutEvent( $loginAuthRequest );

            return $this->sendLockoutResponse( $loginAuthRequest );
        }

        if ( $this->attemptLogin( $loginAuthRequest ) ) {
            return $this->sendLoginResponse( $loginAuthRequest );
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts( $loginAuthRequest );

        return $this->sendFailedLoginResponse( $loginAuthRequest );
    }

    /**
     * Validate the user login request.
     *
     * @param Request $request
     */
    protected function validateLogin( Request $request ): void
    {

        $request->validate( [
            $this->username() => 'required|string',
            'password'        => 'required|string',
        ] );
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username(): string
    {

        return 'username';
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param Request $request
     * @return bool
     */
    protected function attemptLogin( Request $request ): bool
    {

        return $this->guard()->attempt( $this->credentials( $request ), $request->filled( 'remember' ) );
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return mixed
     */
    protected function guard()
    {

        return Auth::guard();
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param Request $request
     * @return array
     */
    protected function credentials( Request $request ): array
    {

        return $request->only( $this->username(), 'password' );
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    protected function sendLoginResponse( Request $request ): RedirectResponse
    {

        $request->session()->regenerate();

        $this->clearLoginAttempts( $request );

        return $this->authenticated( $request, $this->guard()->user() )
            ? : redirect()->intended( $this->redirectPath() );
    }

    /**
     * The user has been authenticated.
     *
     * @param Request $request
     * @param         $user
     */
    protected function authenticated( Request $request, $user ): void
    {
        //
    }

    /**
     * Get the failed login response instance.
     *
     * @param Request $request
     * @throws Exception
     */
    protected function sendFailedLoginResponse( Request $request ): void
    {

        $message = [
            'error' => [ __('Incorrect user or password') ],
            'info' => __('Incorrect password'),
        ];

        throw new Exception( json_encode( $message ), 422 );
    }

    /**
     * Log the user out of the application.
     *
     * @param Request $request
     * @return RedirectResponse|Redirector
     */
    public function logout( Request $request )
    {

        $this->guard()->logout();

        $request->session()->invalidate();

        return $this->loggedOut( $request ) ? : redirect( '/' );
    }

    /**
     * The user has logged out of the application.
     *
     * @param Request $request
     * @return mixed
     */
    protected function loggedOut( Request $request )
    {
        //
    }
}
