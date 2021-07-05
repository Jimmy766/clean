<?php

namespace App\Core\Users\Services;

use App\Core\Rapi\Models\Pixel;

/**
 * Class GetPixelByUserService
 * @package App\Services\User
 */
class GetPixelByUserService
{

    /**
     * @param $request
     * @param $user
     * @return mixed
     */
    public static function execute($request, $user)
    {
        $cookies = $request->usr_cookies;
        $pixels  = collect([]);
        if ($cookies) {
            $affiliate = explode('_', $cookies)[ 0 ];
            $step      = 1;

            if ($request->usr_address1) {
                $step = 2;
            }

            $pixel = Pixel::where('sys_id', $request->client_sys_id)
                ->where('pixel_status', 1)
                ->where('pixel_type', $step)
                ->where('pixel_aff_account', $affiliate)
                ->get();

            $pixel->each(
                function ($item) use ($user, $pixels) {
                    $pixels->push([ 'tag' => $item->PixelTag($user) ]);
                }
            );
        }
        $user->pixels = $pixels;

        return $user;
    }

}
