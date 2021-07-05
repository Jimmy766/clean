<?php


namespace App\Core\Auth\Controllers;

use App\Http\Controllers\ApiController;
use App\Core\Users\Models\User;
use App\Core\Users\Services\FindUserService;
use App\Core\Users\Services\StoreUserFromSocialiteUserService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;


class SocialController extends ApiController
{
    /* Services provided */
    private
        $findUserService,
        $storeUserFromSocialiteUserService;

    public function __construct(
        FindUserService $findUserService,
        StoreUserFromSocialiteUserService $storeUserFromSocialiteUserAndGetUrlService
    )
    {
        $this->middleware('client.credentials');

        /* Provide services */
        $this->findUserService = $findUserService;
        $this->storeUserFromSocialiteUserService = $storeUserFromSocialiteUserAndGetUrlService;
    }

    /**
     * Send the redirection URL for the provider to the frontend
     *
     * This only returns the URL and the framework wraps it in a response automatically.
     *
     * @param  string $provider The provider's lowercase slug  ("google", "facebook"...)
     * @return string           The redirect URL for the provider
     */
    public function redirectToProvider($provider)
    {
        // Catch the redirect
        $redirect = Socialite::driver($provider)->stateless()->redirect();

        // Return the URL only
        return $this->successResponseWithMessage([
            "redirect_url" => $redirect->getTargetUrl(),
        ]);
    }

    /**
     * Public method for provider callbacks (redirect URLs)
     *
     * @param  string                           $provider The provider's
     *                                                    lowercase slug
     *                                                    ("google", "facebook"...)
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleProviderCallback($provider)
    {
        /** @var boolean Flag to inform if the user was created at login */
        $isNewRegister = false;

        // Get user data
        $socialUser = Socialite::driver($provider)->stateless()->user();

        // Check if user already exists
        $user = $this->findUserService->execute($socialUser->email);

        if (is_null($user)) {
            // User doesn't exist, create it using social media data
            $user = $this->storeUserFromSocialiteUserService->execute($socialUser, $provider);

            // Inform that the user was created by this process
            $isNewRegister = true;
        }

        $tokenResult = $user->createToken('Personal Access Token');

        return $this->successResponseWithMessage([
            'access_token' => $tokenResult->accessToken,
            'token_type'   => 'Bearer',
            'user' => $socialUser,
            'register' => $isNewRegister
        ]);
    }
}
