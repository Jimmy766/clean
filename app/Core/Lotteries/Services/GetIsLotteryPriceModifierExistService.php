<?php

namespace App\Core\Lotteries\Services;

use App\Core\Lotteries\Models\Lottery;

class GetIsLotteryPriceModifierExistService
{
    public function execute(Lottery $lottery, $modifierEvaluate): bool
    {
        $lotteriesBoostedJackpot = $lottery->lotteriesBoostedJackpot;

        $lotteriesBoostedJackpot = $lotteriesBoostedJackpot->where('boost_value','!=', 0);
        $existModifier = $lotteriesBoostedJackpot->where('modifier_id', $modifierEvaluate);
        return $existModifier->count() > 0;
    }
}
