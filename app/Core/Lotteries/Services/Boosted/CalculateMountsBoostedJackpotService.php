<?php

namespace App\Core\Lotteries\Services\Boosted;

use App\Core\Base\Traits\CartUtils;

class CalculateMountsBoostedJackpotService
{

    use CartUtils;

    /**
     * @param     $lotteriesBoostedJackpot
     * @param     $lottery
     * @param     $jackpotLottery
     * @param int $countValuesActive
     * @return mixed
     */
    public function execute($lotteriesBoostedJackpot, $lottery, $jackpotLottery, $countValuesActive)
    {
        $lotteriesBoostedJackpot = $lotteriesBoostedJackpot->map(
            $this->mapLotteriesBoostedJackpotGetRelationLotteriesModifierTransform(
                $lottery,
                $jackpotLottery,
                $countValuesActive
            )
        );
        return [ $lotteriesBoostedJackpot, $countValuesActive ];
    }

    private function mapLotteriesBoostedJackpotGetRelationLotteriesModifierTransform(
        $lottery,
        $jackpotLottery,
        &$countValuesActive
    ): callable {
        return function ($item) use ($lottery, $jackpotLottery, &$countValuesActive) {
            $draw_jackpot          = $jackpotLottery;
            $jackpot_apply_max     = $item->jackpot_apply_max;
            $fixed_boost_apply_max = $item->fixed_boost_apply_max;
            $fixed_boost_value     = $item->fixed_boost_value;
            $boost_value           = $item->boost_value;
            $boosted_jackpot       = 0;
            // if apply max is greater than current jackpot, we don't apply.
            if (( $jackpot_apply_max < $draw_jackpot && $jackpot_apply_max > 0 ) && ( $fixed_boost_apply_max < $draw_jackpot && $fixed_boost_apply_max > 0 )) {
                $item->boost_value = $boosted_jackpot;
                return $item;
            }
            // calculate boosted jackpot
            if ($fixed_boost_apply_max > $jackpot_apply_max) {
                // first apply summed boost, then fixed
                $boosted_jackpot = ( $draw_jackpot < $jackpot_apply_max ? $draw_jackpot + $boost_value
                    : ( $draw_jackpot < $fixed_boost_apply_max ? $fixed_boost_value : 0 ) );
            } elseif ($fixed_boost_apply_max < $jackpot_apply_max) {
                // first apply fixed boost, then summed
                $boosted_jackpot = ( $draw_jackpot < $fixed_boost_apply_max ? $fixed_boost_value
                    : ( $draw_jackpot < $jackpot_apply_max ? $draw_jackpot + $boost_value : 0 ) );
            }

            if (request("client_site_id") == 1015 && $lottery->curr_code != 'MXN') {
                $factor = $this->convertCurrency($lottery->curr_code, 'MXN');
                $boosted_jackpot = (integer)($boosted_jackpot * $factor);
            }

            $item->boost_value = $boosted_jackpot;
            if($boosted_jackpot > 0){
                ++$countValuesActive;
            }
            return $item;
        };
    }
}
