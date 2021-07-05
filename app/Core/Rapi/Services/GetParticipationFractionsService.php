<?php

namespace App\Core\Rapi\Services;

/**
 * Class GetParticipationFractionsService
 * @package App\Services
 */
class GetParticipationFractionsService
{

    /**
     * @param $syndicateOrSyndicateRaffle
     * @param $ticketByDraw
     * @return int|string
     */
    public static function execute($syndicateOrSyndicateRaffle, $ticketByDraw)
    {
        $maxDenominator = 1;
        if ($syndicateOrSyndicateRaffle !== null) {
            $participationFractions = $syndicateOrSyndicateRaffle->participations_fractions;
            if ($participationFractions !== null) {
                $fractions = explode(',', $participationFractions);
                foreach ($fractions as $fraction) {
                    [ $numerator, $denominator ] = explode("/", $fraction);
                    if ($denominator > $maxDenominator) {
                        $maxDenominator = $denominator;
                    }
                }
                $stringFraction = $ticketByDraw . "/" . $maxDenominator;
                $division       = $ticketByDraw / $maxDenominator;
                if (is_int($division) === false) {
                    $stringFraction = self::transformDecimalFraction($division);
                }
                return is_int($division) === true ? $division : $stringFraction;
            }
        }
        return $ticketByDraw;
    }

    /**
     * @return string
     */
    public static function transformDecimalFraction($n, $tolerance = 1.e-6)
    {
        $h1 = 1;
        $h2 = 0;
        $k1 = 0;
        $k2 = 1;
        $b  = 1 / $n;
        do {
            $b   = 1 / $b;
            $a   = floor($b);
            $aux = $h1;
            $h1  = $a * $h1 + $h2;
            $h2  = $aux;
            $aux = $k1;
            $k1  = $a * $k1 + $k2;
            $k2  = $aux;
            $b   = $b - $a;
        } while (abs($n - $h1 / $k1) > $n * $tolerance);

        return "$h1/$k1";
    }
}
