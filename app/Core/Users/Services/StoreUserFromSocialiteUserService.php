<?php

namespace App\Core\Users\Services;

use App\Services\User\Illuminate;
use App\Services\User\Symfony;
use App\Core\Users\Models\User;
use App\Core\Users\Services\StoreUserService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Socialite\AbstractUser;

class StoreUserFromSocialiteUserService
{

    private $storeUserService;

    public function __construct(
        StoreUserService $storeUserService
    )
    {
        /* Provide services */
        $this->storeUserService = $storeUserService;
    }

    /**
     * Public method to store a user into database from a Socialite user and a provider
     *
     * @param   AbstractUser    $socialUser            User created by Laravel Socialite
     * @param   string          $provider              Lowercase identifier (ex.: "google")
     * @return  \App\Core\Users\Models\User                        The created user
     *@throws  Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException
     *                                                 Exception with validation message
     * @throws  Illuminate\Validation\ValidationException
     *                                                 Exception with validation array
     */
    public function execute(AbstractUser $socialUser, string $provider)
    {
        /** @var mixed[] User data */
        $userData = $this->mapUserDataByProvider($socialUser, $provider);

        $userPassword = Str::random(8); // Generate a password from random

        /* Obtain values set during middleware's request handling, from the app's request */
        $mockRequest = Request::createFrom(request());

        $mockRequest->merge(
            array_merge(
                $userData,
                [
                    'usr_password' => $userPassword,
                    'usr_password_confirmation' => $userPassword,
                    'country_id' => $mockRequest->client_country_id,
                    'usr_phone' => "99-999-999-9999", // 2020-11-23: New default
                ]
            )
        );

        /* This will return the user or trigger a validation response */
        return $this->storeUserService->execute($mockRequest);
    }

    /**
     * Private method to map Social Media provider data for internal request use
     *
     * @param   AbstractUser    $socialUser  User created by Laravel Socialite
     * @param   string          $provider    Lowercase identifier (ex.: "google")
     * @return  mixed[]                      The mapped data
     */
    private function mapUserDataByProvider(AbstractUser $socialUser, string $provider)
    {
        // Mapping specific data according to social media
        $userDataMappings = [
            "google" =>[
                "usr_name" => "given_name",
                "usr_lastname" => "family_name",
                "usr_email" => "email",
            ],
            "default" => [
                "usr_name" => "name",
                "usr_lastname" => "name", // Some social media do not return a last name
                "usr_email" => "email",
            ],
        ];

        $mappings = array_key_exists($provider, $userDataMappings)
                        ? $userDataMappings[$provider]
                        : $userDataMappings["default"];

        /** @var mixed[] Mapped user data, using mapping as reference */
        $userData = collect($mappings)->mapWithKeys(function ($item, $key) use ($socialUser) {
            return [
                "{$key}" => $socialUser->{$item} ?? $socialUser->user[$item],
            ];
        })->toArray();

        return $userData;
    }
}
