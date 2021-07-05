<?php


namespace App\Core\Casino\Services;


use App\Core\Casino\Models\CasinoGame;
use App\Core\Clients\Models\Client;
use Illuminate\Support\Facades\Auth;

class RedTigerIntegration
{


    /**
     * @param CasinoGame $game
     * @return string
     */
    private static function generateToken(CasinoGame $game){
        //Generate a random string.
        $random = openssl_random_pseudo_bytes(16);
        //Convert the binary data into hexadecimal representation.
        $token = bin2hex($random . Auth::user()->usr_id);

        $game->saveToken($token);

        return $token;

    }

    /**
     * @param CasinoGame $game
     * @param $game_mode
     * @param $language
     * @return string
     */
    public static function getUrl(CasinoGame $game, $game_mode, $language)
    {
        if ($game_mode=='real'){
            $token = self::generateToken($game);
        }
        $search = [
            '{lang}',
            '{token}',
            '{playMode}',
            '{game_code}',
            '{channel}',
            '{hasHistory}',
            '{hasRealPlayButton}',
            '{hasGamble}',
            '{hasRoundId}',
            '{fullScreen}',
            '{hasAutoplayTotalSpins}',
            '{hasAutoplayLimitLoss}',
            '{hasAutoplaySingleWinLimit}',
            '{hasAutoplayStopOnJackpot}',
            '{hasAutoplayStopOnBonus}',
            '{casino}'
        ];

        $replace = [
            $language,
            $game_mode == 'demo' ? '' : 'token=' . $token . '&userId='.Auth::user()->usr_id ."&",
            $game_mode == 'demo' ? 'demo' : 'real',
            $game->code,
            request()->is_mobile ? "M" : "D",
            false, //history
            false, //realPlayBtn
            false,   //Gamble,
            false, //RoundID,
            true, //FullScreen
            true, //hasAutoplayTotalSpins
            true, //hasAutoplayLimitLoss
            true, //hasAutoplaySingleWinLimit
            "false", //hasAutoplayStopOnJackpot
            "false", //hasAutoplayStopOnBonus
            "wintrillions" //Can be wintrillions or lottokings
        ];


        $qstring = $game->provider->configs['QSTRING'] . $game->provider->configs['QSTRING2'] .
            $game->provider->configs['QSTRING3'] . $game->provider->configs['QSTRING4'];

        $QSTRING = str_replace($search, $replace, $qstring);
        return $game->provider->configs['API_LAUNCH_GAME_URL'].$QSTRING;
    }
}
